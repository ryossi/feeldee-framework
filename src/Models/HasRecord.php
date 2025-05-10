<?php

namespace Feeldee\Framework\Models;

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
}
