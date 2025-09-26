<?php

namespace Feeldee\Framework\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Feeldee\Framework\Facades\FDate
 *
 * @method static mixed format(mixed $datetime, $format = null)
 */
class FDate extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Feeldee\Framework\Services\FDateService::class;
    }
}
