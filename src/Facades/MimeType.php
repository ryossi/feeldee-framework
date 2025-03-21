<?php

namespace Feeldee\Framework\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Feeldee\Framework\Facades\MimeType
 *
 * @method static string|bool toExtension($mime_type)
 */
class MimeType extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Feeldee\Framework\Services\MimeTypeService::class;
    }
}
