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
 * 8xxxx:アカウント関連
 * 9xxxx:その他エラー
 */
class ApplicationException extends Exception
{
    public bool $isRollback;

    function __construct(string $message, int $code, array $replace = [], bool $isRollback = true)
    {
        parent::__construct(__('messages.' . $message, $replace), $code);
        $this->isRollback = $isRollback;
    }

    /**
     * カスタムレポート
     *
     * @return bool|null
     */
    public function report()
    {
        Log::warning($this->getMessage());
    }

    /**
     * カスタムレンダリング
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response()->view('errors.application-exception', ['exception' => $this], 405);
    }

    /**
     * アプリケーション例外をスローします。
     * 
     * @param string $message エラーメッセージ
     * @param int $code エラーコード
     * @param bool $isRoolback トランザクションロールバック有無
     */
    public static function throwException(string $message, int $code, bool $isRoolback = true)
    {
        $e = new ApplicationException($message, $code);
        throw $e;
    }
}
