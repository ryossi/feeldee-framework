<?php

namespace Feeldee\Framework\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class HTML implements CastsAttributes
{
    /**
     * HTMLキャストフックコンフィグレーションキー
     */
    const CONFIG_KEY_HTML_CAST_HOOKS = 'feeldee.html_cast_hooks';

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
        // HTMLキャストフック適用
        $hooks = config(self::CONFIG_KEY_HTML_CAST_HOOKS, []);
        foreach ($hooks as $hook) {
            if (class_exists($hook)) {
                $hook = new $hook;
                if (method_exists($hook, 'get')) {
                    $value = $hook->get($model, $key, $value, $attributes);
                }
            }
        }
        return $value;
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
        // HTMLキャストフック適用
        $hooks = config(self::CONFIG_KEY_HTML_CAST_HOOKS, []);
        foreach ($hooks as $hook) {
            if (class_exists($hook)) {
                $hook = new $hook;
                if (method_exists($hook, 'set')) {
                    $value = $hook->set($model, $key, $value, $attributes);
                }
            }
        }
        return $value;
    }
}
