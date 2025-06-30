<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\ValueObjects\ValueObject as Value;
use Feeldee\Framework\ValueObjects\ValueObject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * コンフィグをあらわすモデル
 */
class Config extends Model
{
    use HasFactory, SetUser;

    protected $fillable = ['type', 'value'];

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
            get: fn(string $value, array $attributes) => Config::newValue($attributes['type'])->fromJson($value),
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
        return array_keys(config('feeldee.profile.config.value_objects'));
    }

    /**
     * コンフィグタイプに対応したカスタムコンフィグクラのインスタンスを生成します。
     * 
     * @param string $type コンフィグタイプ
     * @return Value カスタムコンフィグクラのインスタンス
     * @throws ApplicationException コンフィグタイプが未定義の場合、10005エラーをスローします。
     */
    public static function newValue(string $type): Value
    {
        $value_object_classes =  config('feeldee.profile.config.value_objects');
        if (array_key_exists($type, $value_object_classes)) {
            $value_object_class = $value_object_classes[$type];
            return new $value_object_class;
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
