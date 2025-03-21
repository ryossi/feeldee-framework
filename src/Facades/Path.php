<?php

namespace Feeldee\Framework\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Feeldee\Framework\Facades\Path
 *
 * @method static mixed combine(...$path)
 */
class Path extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Feeldee\Framework\Services\PathService::class;
    }
}
