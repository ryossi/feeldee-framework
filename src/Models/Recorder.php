<?php

namespace Feeldee\Framework\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * レコーダをあらわすモデル
 */
class Recorder extends Model
{
    use HasFactory, SetUser;

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
     * モデルの「起動」メソッド
     */
    protected static function booted(): void
    {
        static::addGlobalScope('order_number', function (Builder $builder) {
            $builder->orderBy('order_number');
        });
    }

    /**
     * レコーダを所有するプロフィール
     *
     * @return Attribute
     */
    protected function profile(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->belongsTo(Profile::class, 'profile_id')->get()->first(),
            set: fn($value) => [
                'profile_id' => $value == null ? null : $value->id
            ]
        );
    }

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

    /**
     * タイプを条件に含むようにクエリのスコープを設定
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * 名前を条件に含むようにクエリのスコープを設定
     */
    public function scopeOfName($query, ?string $name)
    {
        return $query->where('name', $name);
    }
}
