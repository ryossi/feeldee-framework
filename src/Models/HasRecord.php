<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Exceptions\ApplicationException;

trait HasRecord
{
    public static function bootHasRecord()
    {
        static::deleting(function (Content $content) {
            // レコード削除
            $content->records()->delete();
        });
    }

    /**
     * レコードリスト
     */
    public function records()
    {
        return $this->hasMany(Record::class, 'content_id');
    }

    /**
     * レコーダを指定してレコードを記録します。
     * 
     * レコーダそのものの他に、レコーダIDでもレコーダ名でも指定可能です。
     * 
     * レコーダまたはレコーダIDを指定する場合は、レコーダ所有プロフィールがコンテンツ所有プロフィールと一致し、かつレコーダタイプがコンテンツ種別と一致している必要があります。
     * 
     * このメソッドは、レコードが存在しない場合は新規作成し、レコードが存在する場合はレコード値のみを更新します。
     * 
     * また、レコード値がnullまたは空文字列の場合は、レコードを削除します。
     * 
     * @param mixed $recorder レコーダまたはレコーダIDまたはレコーダ名
     * @param mixed $value　レコード値
     * @return Record|null レコードまたは削除の場合null
     * @throws ApplicationException レコーダIDに一致するレコーダが見つからない場合、73009エラーをスローします。
     * @throws ApplicationException レコーダ所有プロフィールがコンテンツ所有プロフィールと一致していない場合、73007エラーをスローします。
     * @throws ApplicationException レコーダタイプとコンテンツ種別が一致していない場合、73008エラーをスローします。
     * @throws ApplicationException レコーダ名に一致するレコーダが見つからない場合、73010エラーをスローします。
     */
    public function record(Recorder|int|string $recorder, mixed $value): Record|null
    {
        if (is_string($recorder)) {
            // レコーダ名が指定された場合
            $recorder = $this->profile->recorders()->ofType($this->type())->ofName($recorder)->first();
            if ($recorder === null) {
                throw new ApplicationException(73010, ['type' => $this->type(), 'name' => $recorder]);
            }
        } else {
            if (is_int($recorder)) {
                // レコーダIDが指定された場合
                $recorder = Recorder::find($recorder);
                if ($recorder === null) {
                    // レコーダIDに一致するレコーダが見つからない
                    throw new ApplicationException(73009, ['id' => $recorder]);
                }
            }
            if ($recorder instanceof Recorder) {
                // レコーダが指定された場合
                if ($recorder->profile->id !== $this->profile->id) {
                    // レコーダ所有プロフィールがコンテンツ所有プロフィールと一致していない場合
                    throw new ApplicationException(73007);
                }
                if ($recorder->type !== $this->type()) {
                    // レコーダタイプとコンテンツ種別が一致していない場合
                    throw new ApplicationException(73008);
                }
            }
        }

        // レコードを記録
        return $recorder->record($this, $value);
    }
}
