<?php

namespace Feeldee\Framework\Observers;

use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Category;
use Feeldee\Framework\Models\Content;

class ContentCategoryObserver
{
    protected static function bootedCategory(Content $content)
    {
        if (!is_null($content->category)) {
            if ($content->category instanceof Category) {
                // カテゴリオブジェクトを直接指定している場合
                $content->category_id = $content->category->id;
            } else {
                // カテゴリ名を指定している場合
                $content->refresh();
                $obj = $content->profile->categories()->ofType($content->type())->ofName($content->category)->first();
                if ($obj instanceof Category) {
                    $content->category_id = $obj->id;
                }
            }
            unset($content['category']);
        }
    }

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
        // コンテンツカテゴリ
        static::bootedCategory($content);
        // コンテンツ種別
        static::bootedType($content);
        // コンテンツ所有プロフィール
        static::bootedProfile($content);
    }

    public function updating(Content $content)
    {
        // コンテンツカテゴリ
        static::bootedCategory($content);
        // コンテンツ種別
        static::bootedType($content);
        // コンテンツ所有プロフィール
        static::bootedProfile($content);
    }
}
