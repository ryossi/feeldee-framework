<?php

namespace Feeldee\Framework\Models;

/**
 * Like列挙型
 */
enum Like
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

    /**
     * クエリビルダーを構築します。
     *
     * @param $sql
     * @param string $column カラム名
     * @param string $term 検索語
     * @return void
     */
    public function build($sql, $column, $term)
    {
        switch ($this) {
            case Like::Suffix:
                $sql->where($column, 'like', '%' . $term);
                break;
            case Like::Middle:
                $sql->where($column, 'like', '%' . $term . '%');
                break;
            case Like::Prefix:
                $sql->where($column, 'like', $term . '%');
                break;
            default:
                $sql->where($column, '=', $term);
        }
    }
}
