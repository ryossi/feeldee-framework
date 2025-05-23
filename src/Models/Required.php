<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Exceptions\ApplicationException;
use Illuminate\Database\Eloquent\Model;

trait Required
{
    public static function bootRequired()
    {
        static::creating(function (Model $model) {
            if ($model->required && is_array($model->required)) {
                foreach ($model->required as $key => $value) {
                    if ($model->$key == null && $model->$key == '') {
                        throw new ApplicationException($value);
                    }
                }
            }
        });

        static::updating(function (Model $model) {
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
