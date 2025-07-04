<?php

namespace Feeldee\Framework\ValueObjects\Casts;

use Feeldee\Framework\ValueObjects\Casts\CastsAttributes;
use Feeldee\Framework\ValueObjects\ValueObject;
use Illuminate\Support\Collection;

class CollectionArray implements CastsAttributes
{
    public function get(ValueObject $valueObject, string $key, $value, array $attributes): mixed
    {
        return new Collection($value);
    }

    public function set(ValueObject $valueObject, string $key, $value, array $attributes): mixed
    {
        return $value;
    }
}
