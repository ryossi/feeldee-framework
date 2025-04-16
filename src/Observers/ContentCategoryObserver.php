<?php

namespace Feeldee\Framework\Observers;

use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Content;

class ContentCategoryObserver
{
    /**
     * Handle the Content "creating" event.
     *
     * @param  \Feeldee\Framework\Models\Content  $content
     * @return void
     */
    public function creating(Content $content)
    {
        if (!is_null($content->category)) {
            if ($content->profile != $content->category->profile) {
                // カテゴリ所有プロフィールとコンテンツ所有プロフィールが一致しない場合
                throw new ApplicationException('PostCategoryProfileMissmatch', 20001);
            }
            if ($content->category->type != $content::type()) {
                // カテゴリ種別とコンテンツ種別が一致しない場合
                throw new ApplicationException('PostCategoryTypeMissmatch', 20002);
            }
        }
    }

    /**
     * Handle the Content "updating" event.
     *
     * @param  \Feeldee\Framework\Models\Content  $content
     * @return void
     */
    public function updating(Content $content)
    {
        if (!is_null($content->category)) {
            if ($content->profile != $content->category->profile) {
                // カテゴリ所有プロフィールとコンテンツ所有プロフィールが一致しない場合
                throw new ApplicationException('PostCategoryProfileMissmatch', 20001);
            }
            if ($content->category->type != $content::type()) {
                // カテゴリ種別とコンテンツ種別が一致しない場合
                throw new ApplicationException('PostCategoryTypeMissmatch', 20002);
            }
        }
    }
}
