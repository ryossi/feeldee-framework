<?php

namespace Feeldee\Framework\Casts;

use Feeldee\Framework\Models\Config;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class ConfigValue implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {
        $type = $attributes['type'];
        $value_object = Config::newValue($type);
        $value_object->fromModelAndJson($model, $value);
        return $value_object;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return ['value' => $value->toJson()];
    }
}
