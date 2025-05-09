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
     * 配列に表示する属性
     *
     * @var array
     */
    protected $visible = ['name', 'data_type', 'unit', 'description'];

    /**
     * 複数代入可能な属性
     *
     * @var array
     */
    protected $fillable = ['profile', 'type', 'name', 'data_type', 'unit', 'description', 'order_number'];

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
    protected static function validatedNameDuplicate(Self $model)
    {
        if ($model->profile->recorders()->ofType($model->type)->ofName($model->name)->first()?->id !== $model->id) {
            // レコーダ所有プロフィールとレコーダタイプの中でレコーダ名が重複している場合
            throw new ApplicationException(73005, ['ptofile_id' => $model->profile->id, 'type' => $model->type, 'name' => $model->name]);
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
            static::validatedNameDuplicate($model);
        });

        static::updating(function (Self $model) {
            // レコーダ名重複チェック
            static::validatedNameDuplicate($model);
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
     * レコーダタイプを条件に含むようにクエリのスコープを設定
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * レコーダ名を条件に含むようにクエリのスコープを設定
     */
    public function scopeOfName($query, ?string $name)
    {
        return $query->where('name', $name);
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

    /**
     * レコーダで記録されたレコードリストを取得
     */
    public function records()
    {
        return $this->hasMany(Record::class);
    }

    /**
     * このレコーダを使って値を記録します。
     * 
     * @param Content $content コンテンツ
     * @param mixed $value　記録する値
     * @return ?Record レコード
     */
    public function record(Content $content, mixed $value): ?Record
    {
        $record = $this->records()->where('content_id', $content->id)->first();
        if ($record === null) {
            if ($value !== null && $value !== "") {
                // 値が空でない場合のみレコード追加
                $record = $this->records()->create([
                    'content' => $content,
                    'value' => $value
                ]);
            }
        } else {
            if ($value === null || $value === "") {
                // 値が空の場合レコード削除
                $record->delete();
            } else {
                // 値更新
                $record->value = $value;
                $record->save();
            }
        }
        return $record;
    }
}
