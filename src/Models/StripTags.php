<?php

namespace Feeldee\Framework\Models;

use Illuminate\Database\Eloquent\Model;

trait StripTags
{
    public static function bootStripTags()
    {
        static::saving(function (Model $model) {
            if ($model->strip_tags && is_array($model->strip_tags)) {
                foreach ($model->strip_tags as $key => $value) {
                    $model->$value = strip_tags($model->$key);
                }
            }
        });
    }
}
