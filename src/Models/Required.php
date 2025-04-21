<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Exceptions\ApplicationException;

trait Required
{
    public static function bootRequired()
    {
        static::saving(function (Content $model) {
            if ($model->required && is_array($model->required)) {
                foreach ($model->required as $key => $value) {
                    if ($model->$key == null && $model->$key == '') {
                        throw new ApplicationException($value);
                    }
                }
            }
        });
    }
}
