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

    public static function values(): array
    {
        return array_column(self::cases(), 'name', 'value');
    }

    /**
     * 公開レベルのラベルを取得します。
     * 
     * @return string ラベル
     */
    public function label(): string
    {
        return config('feeldee.content.public_level')[$this->value];
    }
}
