<?php

namespace Feeldee\Framework\Models;

trait HasTag
{
    public static function bootHasTag()
    {
        static::deleting(function (Content $content) {
            // タグ付け解除
            $content->tags()->detach();
        });
    }

    /**
     * コンテンツタグリスト
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable')->withTimestamps();
    }
}
