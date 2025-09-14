<?php

namespace Feeldee\Framework\Services;

use Carbon\Carbon;
use Carbon\CarbonImmutable;

class FDateService
{

    /**
     * 日付の拡張フォーマット関数
     * 
     * フォーマット一覧
     *
     * - +00:00:00・・・日付文字列の時刻の不足部分をフォーマットの時分秒で補完する
     * 
     * @param mixed $datetime 日付文字列
     * @param string|null $format フォーマット
     * @return mixed
     */
    public function format(mixed $datetime, $format = null): mixed
    {
        if (is_string($datetime)) {
            // $formatが+00:00:00形式の文字列の場合
            if (preg_match('/^[+-]\d{2}:\d{2}:\d{2}$/', (string)$format)) {
                $format = ltrim($format, '+');
                $split = explode(':', $format);
                if (count($split) === 3) {
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $datetime)) {
                        $datetime .= " $format";
                    } elseif (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}$/', $datetime)) {
                        $split = explode(':', $format);
                        $datetime .= ':' . $split[1] . ':' . $split[2];
                    } elseif (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $datetime)) {
                        $datetime .= ':' . $split[2];
                    }
                }
            }
        } elseif ($datetime instanceof Carbon || $datetime instanceof CarbonImmutable) {
            // Carbonインスタンスの場合は、フォーマットして文字列に変換
            $datetime = $datetime->format('Y-m-d H:i:s');
        }
        return $datetime;
    }
}
