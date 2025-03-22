<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Observers\SetUserObserver;

trait SetUser
{
    public static function bootSetUser()
    {
        self::observe(SetUserObserver::class);
    }
}
