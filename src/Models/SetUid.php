<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Observers\SetUidObserver;

trait SetUid
{
    public static function bootSetUid()
    {
        self::observe(SetUidObserver::class);
    }
}
