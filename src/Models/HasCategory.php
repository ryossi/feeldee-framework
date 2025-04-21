<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Observers\ContentCategoryObserver;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasCategory
{
    public static function bootHasCategory()
    {
        self::observe(ContentCategoryObserver::class);
    }

    /**
     * コンテンツカテゴリ
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
