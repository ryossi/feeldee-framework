<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\ValueObjects\ValueObject as Value;
use Feeldee\Framework\ValueObjects\ValueObject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

/**
 * コンフィグをあらわすモデル
 */
class Config extends Model
{
    use HasFactory, SetUser;

    protected $fillable = ['type', 'value'];

    /**
     * カスタムコンフィグクラス定義コンフィグレーションキー
     */
    public const CONFIG_KEY_VALUE_OBJECTSY = 'feeldee.profile.config.value_objects';

    /**
     * モデルの「起動」メソッド
     */
    protected static function booted(): void
    {
        // 作成時にvalueが設定されていない場合は、デフォルト値を設定
        static::creating(function (Self $model) {
            if (empty($model->attributes['value'])) {
                $model->attributes['value'] = $model->newValue($model->type);
            }
        });
    }

    /**
     * コンフィグ所有プロフィール
     */
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * コンフィグ値
     */
    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn(string $value, array $attributes) => $this->newValue($attributes['type'])->fromJson($value),
            set: fn(ValueObject $value) => ['value' => $value->toJson()],
        );
    }

    /**
     * 定義済みコンフィグタイプの配列を取得します。
     * 
     * @return array 定義済みコンフィグタイプの配列
     */
    public static function getTypes(): array
    {
        return array_keys(config(self::CONFIG_KEY_VALUE_OBJECTSY) ?? []);
    }

    /**
     * コンフィグタイプに対応したカスタムコンフィグクラのインスタンスを生成します。
     * 
     * @param string $type コンフィグタイプ
     * @return Value カスタムコンフィグクラのインスタンス
     * @throws ApplicationException コンフィグタイプが未定義の場合、10005エラーをスローします。
     */
    protected function newValue(string $type): Value
    {
        $value_object_classes =  config(self::CONFIG_KEY_VALUE_OBJECTSY) ?? [];
        if (array_key_exists($type, $value_object_classes)) {
            $value_object_class = $value_object_classes[$type];
            $reflection = new ReflectionClass($value_object_class);
            $constructor = $reflection->getConstructor();
            $params = $constructor->getParameters();
            $firstParam = reset($params);
            if ($firstParam && $firstParam->getType() && $firstParam->getType()->getName() === self::class) {
                // 最初のパラメータがモデルを受け取る場合は、モデルを渡す
                return app()->makeWith($value_object_class, ['model' => $this]);
            }
            return app()->makeWith($value_object_class);
        }
        throw new ApplicationException(10005, ['type' => $type]);
    }

    /**
     * コンフィグタイプを条件に含むようにクエリスコープを設定
     */
    public function scopeOfType(Builder $query, string $type): void
    {
        $query->where('type', $type);
    }
}
