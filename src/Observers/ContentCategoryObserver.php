<?php

namespace Feeldee\Framework\Observers;

use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Content;

class ContentCategoryObserver
{
    protected static function bootedProfile(Content $content)
    {
        if (!is_null($content->category) && $content->profile->id !== $content->category->profile->id) {
            // カテゴリ所有プロフィールとコンテンツ所有プロフィールが一致しない場合
            throw new ApplicationException('CategoryContentProfileMissmatch', 71006);
        }
    }

    protected static function bootedType(Content $content)
    {
        if (!is_null($content->category) && $content->category->type !== $content::type()) {
            // カテゴリ種別とコンテンツ種別が一致しない場合
            throw new ApplicationException('CategoryContentTypeMissmatch', 71007);
        }
    }

    public function creating(Content $content)
    {
        // コンテンツ種別
        static::bootedType($content);
        // コンテンツ所有プロフィール
        static::bootedProfile($content);
    }

    public function updating(Content $content)
    {
        // コンテンツ種別
        static::bootedType($content);
        // コンテンツ所有プロフィール
        static::bootedProfile($content);
    }
}
