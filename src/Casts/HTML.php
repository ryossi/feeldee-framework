<?php

namespace Feeldee\Framework\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class HTML implements CastsAttributes
{
    /**
     * HTMLフックコンフィグレーションキー
     */
    const CONFIG_KEY_HTML_HOOKS = 'feeldee.html_hooks';

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
        // HTMLフック適用
        $hooks = config(self::CONFIG_KEY_HTML_HOOKS, []);
        foreach ($hooks as $hookKey => $hookClass) {
            if (strpos($key, $hookKey) === 0 && class_exists($hookClass)) {
                $hook = new $hookClass;
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
        // HTMLフック適用
        $hooks = config(self::CONFIG_KEY_HTML_HOOKS, []);
        foreach ($hooks as $hookKey => $hookClass) {
            if (strpos($key, $hookKey) === 0 && class_exists($hookClass)) {
                $hook = new $hookClass;
                if (method_exists($hook, 'set')) {
                    $value = $hook->set($model, $key, $value, $attributes);
                }
            }
        }
        return $value;
    }
}
