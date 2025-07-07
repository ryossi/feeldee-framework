<?php

namespace Tests\Hooks;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class CustomHtmlHook implements CastsAttributes
{

    const PREFIX = 'custom_html_';

    public function get(
        Model $model,
        string $key,
        mixed $value,
        array $attributes,
    ): string {
        return self::PREFIX . $value;
    }

    public function set(
        Model $model,
        string $key,
        mixed $value,
        array $attributes,
    ): string {
        return str_starts_with($value, self::PREFIX) ? substr($value, strlen(self::PREFIX)) : $value;
    }
}
