<?php

namespace Feeldee\Framework\ValueObjects;

use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use stdClass;

abstract class ValueObject implements JsonSerializable, Jsonable
{
    protected $fillable = [];

    protected $excludes = [];

    protected $casts = [];

    protected $json = null;

    public $model = null;

    public function __construct() {}

    public function fromModelAndJson(mixed $model, mixed $value)
    {
        $this->model = $model;
        $this->fromJson($value);
    }

    public function fromJson(mixed $value)
    {
        if ($value instanceof stdClass) {
            $stdObj = $value;
        } else {
            $stdObj = json_decode($value);
        }
        $vars = get_object_vars($this);
        array_push($this->excludes, 'fillable', 'excludes', 'casts', 'json', 'model');
        foreach ($vars as $key => $value) {
            if (!in_array($key, $this->excludes)) {
                if (array_key_exists($key, $this->casts)) {
                    $cast = new $this->casts[$key];
                    $this->{$key} = $cast->get($this, $key, $stdObj->{$key}, get_object_vars($stdObj));
                } else {
                    if ($stdObj->{$key} instanceof stdClass) {
                        if (property_exists($stdObj, $key)) {
                            $value->fromJson($stdObj->{$key});
                        }
                        $this->{$key} = $value;
                    } else {
                        $this->{$key} = property_exists($stdObj, $key) ? $stdObj->{$key} : $value;
                    }
                }
            }
        }
    }

    public function fill(array $attributes)
    {
        if ($this->fillable) {
            foreach ($this->fillable as $key) {
                if (array_key_exists($key, $attributes)) {
                    $this->{$key} = $attributes[$key];
                }
            }
            $this->json = json_encode($this);
        }
    }

    public function toJson($options = 0)
    {
        $this->json = json_encode($this, $options);
        return $this->json;
    }

    public function jsonSerialize(): mixed
    {
        $vars = get_object_vars($this);
        unset($vars['fillable']);
        unset($vars['excludes']);
        unset($vars['casts']);
        unset($vars['json']);
        unset($vars['model']);
        foreach ($this->excludes as $exclude) {
            unset($exclude);
        }
        foreach ($vars as $key => $value) {
            if (array_key_exists($key, $this->casts)) {
                $cast = new $this->casts[$key];
                $vars[$key] = $cast->set($this, $key, $value, $vars);
            }
        }
        return $vars;
    }

    public function __toString()
    {
        return $this->toJson();
    }
}
