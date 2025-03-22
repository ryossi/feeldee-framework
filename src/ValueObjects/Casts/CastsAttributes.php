<?php

namespace Feeldee\Framework\ValueObjects\Casts;

interface CastsAttributes
{
    /**
     * Json値をオブジェクト値へ変換
     *
     * @param  \Feeldee\Framework\ValueObjects\ValueObject  $valuObject
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($valuObject, string $key, $value, array $attributes);

    /**
     * 値をJson値に変換
     *
     * @param  \Feeldee\Framework\ValueObjects\ValueObject  $valuObject
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($valuObject, string $key, $value, array $attributes);
}
