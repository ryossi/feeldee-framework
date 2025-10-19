<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Exceptions\ApplicationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * レコーダをあらわすモデル
 */
class Recorder extends Model
{
    use HasFactory, Required, SetUser;

    /**
     * 複数代入可能な属性
     *
     * @var array
     */
    protected $fillable = ['profile', 'type', 'name', 'image', 'data_type', 'unit', 'description'];

    /**
     * 配列に表示する属性
     *
     * @var array
     */
    protected $visible = ['name', 'image', 'data_type', 'unit', 'description'];

    /**
     * 必須にする属性
     * 
     * @var array
     */
    protected $required = [
        'profile_id' => 73001,
        'type' => 73002,
        'name' => 73003,
        'data_type' => 73006,
    ];

    /**
     * レコーダ名重複チェック
     * 
     * @param Self $model モデル
     * @return void
     * @throws ApplicationException レコーダ所有プロフィールとレコーダタイプの中でレコーダ名が重複している場合、73005エラーをスローします。
     */
    protected static function validateNameDuplicate(Self $model)
    {
        if ($model->profile->recorders()->of($model->type)->name($model->name)->first()?->id !== $model->id) {
            // レコーダ所有プロフィールとレコーダタイプの中でレコーダ名が重複している場合
            throw new ApplicationException(73005, ['ptofile_id' => $model->profile->id, 'type' => $model->type, 'name' => $model->name]);
        }
    }

    /**
     * レコーダ表示順決定
     * 
     * 同じレコーダ所有プロフィール、レコーダタイプでレコーダの表示順で最後に並ぶようレコーダ表示順を自動採番します。
     * 
     * @param Self $model モデル
     * @return void
     */
    protected static function decideOrderNumber(Self $model)
    {
        // 同一タイプの全てのレコーダリスト取得
        $tag_list = $model->profile->recorders()->of($model->type)->get();

        // 表示順生成
        if ($tag_list->isEmpty()) {
            $model->order_number = 1;
        } else {
            $last = $tag_list->last();
            $model->order_number = $last->order_number + 1;
        }

        if (!$model->profile) {
            // レコー所有プロフィールが存在しない場合
            return;
        }
    }

    /**
     * モデルの「起動」メソッド
     */
    protected static function booted(): void
    {
        static::addGlobalScope('order_number', function (Builder $builder) {
            $builder->orderBy('order_number');
        });

        static::creating(function (Self $model) {
            // レコーダ名重複チェック
            static::validateNameDuplicate($model);
            // レコーダ表示順決定
            static::decideOrderNumber($model);
        });

        static::updating(function (Self $model) {
            // レコーダ名重複チェック
            static::validateNameDuplicate($model);
        });

        static::deleting(function (Self $model) {
            // レコード削除
            $model->records()->delete();
        });
    }

    /**
     * レコーダ所有プロフィール
     *
     * @return BelongsTo
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * レコードリスト
     */
    public function records()
    {
        return $this->hasMany(Record::class);
    }

    /**
     * 表示順で一つ前のレコーダを取得します。
     *
     * @return mixed 一つ前のレコーダ。存在しない場合null
     */
    public function previous(): mixed
    {
        return $this->where('profile_id', '=', $this->profile->id)
            ->where('order_number', '<', $this->order_number)->orderBy('order_number', 'desc')->first();
    }

    /**
     * 表示順で一つ後のレコーダを取得します。
     * 
     * @return mixed 一つ後のレコーダ。存在しない場合null
     */
    public function next(): mixed
    {
        return $this->where('profile_id', '=', $this->profile->id)
            ->where('order_number', '>', $this->order_number)->orderBy('id', 'asc')->first();
    }

    /**
     * レコーダの表示順を一つ上げます。
     * 表示順が既に先頭の場合は、何もしません（空振り）。
     *
     * @return void
     */
    public function orderUp(): void
    {
        $target = $this->previous();
        if ($target) {
            // 一つ前のタグが存在する場合
            // 表示順を入れ替え
            $prev = $target->order_number;
            $target->order_number = $this->order_number;
            $this->order_number = $prev;

            $target->save();
            $this->save();
        }
    }

    /**
     * レコーダの表示順を一つ下げます。
     * 表示順が既に最後の場合は、何もしません（空振り）。
     *
     * @return void
     */
    public function orderDown(): void
    {
        $target = $this->next();
        if ($target) {
            // 一つ後のレコーダが存在する場合
            // 表示順を入れ替え
            $prev = $target->order_number;
            $target->order_number = $this->order_number;
            $this->order_number = $prev;

            $target->save();
            $this->save();
        }
    }

    /**
     * レコーダ所有者による絞り込みのためのローカルスコープ
     * 
     * @param Builder $query
     * @param string|Profile|null $profile プロフィールまたはニックネーム
     * @return void
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコーダ所有者による絞り込み
     */
    public function scopeBy($query, string|Profile|null $profile): void
    {
        if ($profile instanceof Profile) {
            $query->where('profile_id', $profile->id);
        } elseif (is_string($profile)) {
            $query->whereHas('profile', function ($q) use ($profile) {
                $q->where('nickname', $profile);
            });
        }
    }

    /**
     * レコーダタイプによる絞り込みのためのローカルスコープ
     * 
     * @param Builder $query
     * @param Post|string $type レコーダタイプ
     * @return void
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコーダタイプによる絞り込み
     */
    public function scopeOf($query, Post|string $type)
    {
        if (is_subclass_of($type, Post::class)) {
            $type = $type::type();
        }
        return $query->where('type', $type);
    }

    /**
     * レコーダ名による絞り込みのためのローカルスコープ
     * 
     * @param Builder $query
     * @param string|null $name レコーダ名
     * @param Like $like LIKE列挙型（デフォルトは、完全一致）
     * @return void
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコーダ名による絞り込み
     */
    public function scopeName($query, ?string $name, Like $like = Like::All): void
    {
        $like->build($query, 'name', $name);
    }

    /**
     * 投稿を指定してレコードを記録します。
     * 
     * このメソッドは、レコードが存在しない場合は新規作成し、レコードが存在する場合はレコード値のみを更新します。
     * 
     * また、レコード値がnullまたは空文字列の場合は、レコードを削除します。
     * 
     * @param Post $post 投稿
     * @param mixed $value　レコード値
     * @return Record|null レコードまたは削除の場合null
     */
    public function record(Post $post, mixed $value): Record|null
    {
        $record = $this->records()->for($post)->first();
        if ($record === null) {
            if ($value !== null && $value !== "") {
                // 値が空でない場合のみレコード追加
                $record = $this->records()->create([
                    'post' => $post,
                    'value' => $value
                ]);
            }
        } else {
            if ($value === null || $value === "") {
                // 値が空の場合レコード削除
                $record->delete();
                return null;
            } else {
                // 値更新
                $record->value = $value;
                $record->save();
            }
        }
        return $record;
    }

    // ========================== ここまで整理済み ==========================

    /**
     * 現在のレコーダリストを新しいレコーダリストに従って全て入れ替えます。
     * 存在しない名前のレコーダは、新規作成します。
     * 名前が一致するレコーダは、更新して表示順を振り直します。
     * 新しいレコーダリストに存在しないレコーダは削除します。
     * 
     * @param Profile $profile プロフィール
     * @param string $type タイプ
     * @param array $newRecorders 新しいレコーダリスト
     * @return void
     */
    public static function replaceAll(Profile $profile, string $type, array $newRecorders): void
    {
        $order_number = 0;
        foreach ($newRecorders as $newRecorder) {

            // 表示順は、リストの先頭から順番
            $order_number++;

            // タイプと名前に一致するレコーダ取得
            $recorder = Recorder::where('profile_id', $profile->id)->where('type', $type)->where('name', $newRecorder['name'])->first();

            if (!$recorder) {
                // レコーダが存在しない場合、新規作成
                $recorder = Recorder::create([
                    'profile' => $profile,
                    'type' => $type,
                    'name' => $newRecorder['name'],
                    'data_type' => $newRecorder['data_type'],
                    'unit' => $newRecorder['unit'],
                    'description' => $newRecorder['description'],
                    'order_number' => $order_number
                ]);
            } else {
                // レコーダが存在する場合、更新
                $recorder->name = $newRecorder['name'];
                $recorder->unit = $newRecorder['unit'];
                $recorder->description = $newRecorder['description'];
                $recorder->order_number = $order_number;
                $recorder->save();
            }
        }

        // 新しいレコードリストに含まれないレコードは削除
        $deleteNames = array_column($newRecorders, 'name');
        $deleteRecorders = Recorder::where('profile_id', $profile->id)->where('type', $type)->whereNotIn('name', $deleteNames)->get();
        foreach ($deleteRecorders as $deleteRecorder) {
            $deleteRecorder->delete();
        }
    }
}
