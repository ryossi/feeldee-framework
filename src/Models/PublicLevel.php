<?php

namespace Feeldee\Framework\Models;

/**
 * 公開レベルをあらわす列挙型
 */
enum PublicLevel: int
{
/**
     * 自分
     */
    case Private = 0;

/**
     * 友達
     */
    case Friend = 2;

/**
     * 会員
     */
    case Member = 5;

/**
     * 全員
     */
    case Public = 10;

    /**
     * 公開レベル配列取得
     * 
     * すべての公開レベルとその値の組み合わせを連想配列を取得します。
     * 
     * @return array レベルとラベルの連想配列
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'name', 'value');
    }

    /**
     * 公開レベルラベル配列取得
     * 
     * すべての公開レベルとそのラベルの組み合わせを連想配列を取得します。
     * 
     * @return array レベルとラベルの連想配列
     */
    public static function labels(): array
    {
        $config = config('feeldee.public_level.label');
        $values = self::values();
        if (!empty($config)) {
            $values = array_replace($values, $config);
        }
        return $values;
    }

    /**
     * 公開レベルラベル取得
     * 
     * 公開レベルのラベルを取得します。
     * 
     * @return string ラベル
     */
    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
