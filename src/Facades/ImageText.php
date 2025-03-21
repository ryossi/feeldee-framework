<?php

namespace Feeldee\Framework\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Feeldee\Framework\Facades\ImageText
 *
 * @method static string resize(string $text, mixed $width = null, mixed $height = null, int $quality = 90)
 * @method static bool isImageText(?string $text)
 */
class ImageText extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Feeldee\Framework\Services\ImageTextService::class;
    }
}
