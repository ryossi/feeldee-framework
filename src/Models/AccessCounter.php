<?php

namespace Feeldee\Framework\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Relation;

trait AccessCounter
{
    /**
     * アクセス履歴総数を取得します。
     * 
     * @param $params プロフィールの場合、コンテンツ種別および作成日時（today|yesterday|指定日時）
     * コンテンツの場合、作成日時（today|yesterday|指定日時）
     * @return int アクセス履歴総数（取得できない場合0）
     */
    public function countOfAccess(...$params): int
    {
        $type = null;
        $created_by = null;
        if ($this instanceof Profile) {
            $type = sizeof($params) > 0 ? $params[0] : null;
            if (!Relation::getMorphedModel($type)) {
                $created_by = $type;
            }
            $created_by = sizeof($params) > 1 ? $params[1] : null;
        } else if ($this instanceof Content) {
            $created_by = sizeof($params) > 0 ? $params[0] : null;
        }
        if (method_exists($this, 'viewHistories')) {
            $sql = $this->viewHistories();
            if ($type !== null) {
                // コンテンツ種別による絞り込み
                $sql->where('content_type', $type)->count();
            }
            if ($created_by !== null) {
                if ($created_by == 'today') {
                    // 作成日時による絞り込み（today）
                    $date = Carbon::today();
                } else if ($created_by == 'yesterday') {
                    // 作成日時による絞り込み（yesterday）
                    $date = Carbon::yesterday();
                } else {
                    // 指定日時による絞り込み
                    $date = new Carbon($created_by);
                }
                $start = $date->copy()->startOfDay();
                $end = $date->copy()->endOfDay();
                $sql->whereBetween('viewed_at', [$start, $end]);
            }
            return $sql->count();
        }
        return 0;
    }
}
