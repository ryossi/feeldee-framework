<?php

namespace Feeldee\Framework\Services;

use Feeldee\Framework\Models\Content;
use Carbon\Carbon;

class ContentViewHistoryService
{
    /**
     * コンテンツ閲覧履歴を登録します。
     * 
     * @return Content $content コンテンツ
     */
    public function regist(Content $content): void
    {
        $content->viewHistories()->create(['viewed_at' => Carbon::now()]);
    }
}
