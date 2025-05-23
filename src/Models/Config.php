<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Casts\ConfigValue;
use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\ValueObjects\ValueObject;
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
     * キャストする必要のある属性
     *
     * @var array
     */
    protected $casts = [
        'value' => ConfigValue::class,
    ];

    /**
     * コンフィグ所有プロフィール
     */
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * 新しい値オブジェクトを生成します。
     * 
     * @param string $type タイプ
     * @return ValueObject 値オブジェクト
     * @throws ApplicationException コンフィグタイプが未定義の場合、10005エラーをスローします。
     */
    public static function newValue(string $type): ValueObject
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
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
