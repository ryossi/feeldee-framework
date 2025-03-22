<?php

namespace Feeldee\Framework\ValueObjects\Casts;

use Feeldee\Framework\ValueObjects\Casts\CastsAttributes;
use Illuminate\Support\Collection;

class CollectionArray implements CastsAttributes
{
    public function get($valuObject, string $key, $value, array $attributes)
    {
        return new Collection($value);
    }

    public function set($valuObject, string $key, $value, array $attributes)
    {
        return $value;
    }
}
