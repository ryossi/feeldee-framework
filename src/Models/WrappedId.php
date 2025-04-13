<?php

namespace Feeldee\Framework\Models;

trait WrappedId
{

    public static function bootWrappedId()
    {
        static::creating(function ($model) {
            foreach ($model->wrappable as $key => $value) {
                if (isset($model->$key) && array_key_exists($key, $model->attributes)) {
                    if (is_object($model->$key)) {
                        // 属性にリレーション名が直接入っている場合は、リレーションからIDを取得してセットする
                        $model->$value = $model->attributes[$key]->id;
                    } else {
                        // リレーションからIDを取得してセットする
                        $model->$value = $model->attributes[$key];
                    }
                    // 属性に含まれるリレーション名を削除する
                    // これをしないと、リレーション名がそのままDBに保存されてしまう
                    unset($model->attributes[$key]);
                }
            }
        });
    }

    public function __set($key, $value)
    {
        if (array_key_exists($key, $this->wrappable)) {
            $relation = $this->$key();
            if (method_exists($relation, 'associate')) {
                // リレーションにモデルをセットする
                // これをしないと、リレーション名がそのままDBに保存されてしまう
                return $relation->associate($value);
            }
        }
        parent::__set($key, $value);
    }

    public function __get($key)
    {
        if (array_key_exists($key, $this->wrappable)) {
            $relation = $this->$key();
            if (method_exists($relation, 'get')) {
                // リレーションからモデルを取得する
                return $relation->get()->first();
            }
        }
        return parent::__get($key);
    }
}
