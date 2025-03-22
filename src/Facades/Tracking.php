<?php

namespace Feeldee\Framework\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Feeldee\Framework\Facades\Tracking
 *
 * @method static void start()
 * @method static string uid()
 */
class Tracking extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Feeldee\Framework\Services\TrackingService::class;
    }
}
