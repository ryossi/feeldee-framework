<?php

namespace Feeldee\Framework\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


trait SetUser
{
    public static function bootSetUser()
    {
        static::creating(function (Model $model) {
            $id = Auth::id();
            $model->created_by = $id;
            $model->updated_by = $id;
        });

        static::updating(function (Model $model) {
            $model->updated_by = Auth::id();
        });
    }
}
