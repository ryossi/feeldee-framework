<?php

namespace Feeldee\Framework\Models;

trait WrappedId
{

    public function __set($key, $value)
    {
        if (array_key_exists($key, $this->wrappable)) {
            $relation = $this->$key();
            if (method_exists($relation, 'associate')) {
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
                return $relation->get()->first();
            }
        }
        return parent::__get($key);
    }
}
