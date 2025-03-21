<?php

namespace Feeldee\Framework\Models;

/**
 * SQLのLike条件を生成するビルダーです。
 */
enum SqlLikeBuilder
{
    /**
     * 前方一致
     */
    case Prefix;

    /**
     * 部分一致
     */
    case Middle;

    /**
     * 後方一致
     */
    case Suffix;

    /**
     * 完全一致
     */
    case All;

    public function build($sql, $column, $term)
    {
        switch ($this) {
            case SqlLikeBuilder::Suffix:
                $sql->where($column, 'like', '%' . $term);
                break;
            case SqlLikeBuilder::Middle:
                $sql->where($column, 'like', '%' . $term . '%');
                break;
            case SqlLikeBuilder::Prefix:
                $sql->where($column, 'like', $term . '%');
                break;
            default:
                $sql->where($column, '=', $term);
        }
    }
}
