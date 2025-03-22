<?php

namespace Feeldee\Framework\Models;

/**
 * 写真タイプをあらわす列挙型
 */
enum PhotoType: string
{
    /**
     * Feeldee
     */
    case Feeldee = 'feeldee';

    /**
     * Google
     */
    case Google = 'google';

    /**
     * その他
     */
    case Other = 'other';
}
