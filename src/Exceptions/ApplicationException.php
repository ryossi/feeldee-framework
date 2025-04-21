<?php

namespace Feeldee\Framework\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * アプリケーション共通例外
 * 
 * == コード定義 ==
 * 1xxxx:プロフィール関連
 * 2xxxx:投稿関連
 * 3xxxx:写真関連
 * 4xxxx:場所関連
 * 5xxxx:アイテム関連
 * 6xxxx:コメント関連
 * 71xxx:カテゴリー関連
 * 72xxx:タグ関連
 * 73xxx:レコード関連
 */
class ApplicationException extends Exception
{
    public bool $isRollback;

    function __construct(int $code, array $replace = [], bool $isRollback = true)
    {
        parent::__construct(__('feeldee::messages.' . $code, $replace), $code);
        $this->isRollback = $isRollback;
    }
}
