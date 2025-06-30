<?php

namespace Feeldee\Framework\ValueObjects;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Model;
use JsonSerializable;
use stdClass;

abstract class ValueObject implements JsonSerializable, Jsonable
{
    protected $fillable = [];

    protected $excludes = [];

    protected $casts = [];

    protected $json = null;

    protected $model = null;

    public function __construct(?Model $model = null)
    {
        $this->model = $model;
    }

    /**
     * デシリアライズ
     * 
     * ValueObjectをJSON形式の文字列から復元します。
     * 
     * @param mixed $value JSON形式の文字列またはstdClassオブジェクト
     * @return ValueObject 復元されたValueObjectインスタンス
     */
    public function fromJson(mixed $value): ValueObject
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

        return $this;
    }

    /**
     * ValueObjectの属性を設定します。
     * 
     * このメソッドは、$fillableプロパティに定義された属性のみを設定します。
     * 
     * @param array $attributes 設定する属性の連想配列
     * @return void
     */
    public function fill(array $attributes)
    {
        if ($this->fillable) {
            foreach ($this->fillable as $key) {
                if (array_key_exists($key, $attributes)) {
                    $this->{$key} = $attributes[$key];
                }
            }
        }
    }

    /**
     * シリアライズ
     * 
     * ValueObjectをJSON形式の文字列に変換します。
     * 
     * @param int $flags json_encodeのフラグと同じ値を指定できます。
     * @return string JSON形式の文字列
     */
    public function toJson($flags = 0)
    {
        $this->json = json_encode($this, $flags);
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
            unset($vars[$exclude]);
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
