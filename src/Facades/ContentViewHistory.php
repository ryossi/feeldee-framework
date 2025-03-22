<?php

namespace Feeldee\Framework\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Feeldee\Framework\Facades\ContentViewHistory
 *
 * @method static void regist(Content $content)
 */
class ContentViewHistory extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Feeldee\Framework\Services\ContentViewHistoryService::class;
    }
}
