<?php

namespace Feeldee\Framework\ValueObjects\Casts;

use Feeldee\Framework\ValueObjects\ValueObject;

interface CastsAttributes
{
    /**
     * Json値をオブジェクト値へ変換
     *
     * @param  \Feeldee\Framework\ValueObjects\ValueObject  $valueObject
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get(ValueObject $valueObject, string $key, $value, array $attributes): mixed;

    /**
     * 値をJson値に変換
     *
     * @param  \Feeldee\Framework\ValueObjects\ValueObject  $valueObject
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     
     */
    public function set(ValueObject $valueObject, string $key, $value, array $attributes): mixed;
}
