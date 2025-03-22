<?php

namespace Feeldee\Framework\Observers;

use Feeldee\Framework\Models\Reply;
use Carbon\Carbon;

class ReplyObserver
{
    /**
     * Handle the Reply "creating" event.
     *
     * @param  \Feeldee\Framework\Models\Reply  $reply
     * @return void
     */
    public function creating(Reply $reply)
    {
        // 返信日時未設定時はシステム日時
        if (is_null($reply->replied_at)) {
            $reply->replied_at = Carbon::now();
        }

        // 作成時の返信の公開フラグは、返信対象のコメントと同じ
        $reply->is_public = $reply->comment->isPublic();
    }
}
