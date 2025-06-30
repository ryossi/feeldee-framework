<?php

namespace Feeldee\Framework\ValueObjects\Casts;

use Feeldee\Framework\ValueObjects\Casts\CastsAttributes;
use Feeldee\Framework\ValueObjects\ValueObject;
use Illuminate\Support\Collection;

class CollectionArray implements CastsAttributes
{
    public function get(ValueObject $valuObject, string $key, $value, array $attributes): mixed
    {
        return new Collection($value);
    }

    public function set(ValueObject $valuObject, string $key, $value, array $attributes)
    {
        return $value;
    }
}
