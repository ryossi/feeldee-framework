<?php

namespace Feeldee\Framework\Observers;

use Feeldee\Framework\Models\Comment;
use Carbon\Carbon;

class CommentObserver
{
    /**
     * Handle the Comment "creating" event.
     *
     * @param  \Feeldee\Framework\Models\Comment  $comment
     * @return void
     */
    public function creating(Comment $comment)
    {
        // コメント対象コンテンツのプロフィールコピー
        $comment->profile = $comment->commentable->profile;
    }

    /**
     * Handle the Comment "updated" event.
     *
     * @param  \Feeldee\Framework\Models\Comment  $comment
     * @return void
     */
    public function updating(Comment $comment)
    {
        // 全ての返信の公開フラグをコメントと合わせる
        foreach ($comment->replies as $reply) {
            $reply->is_public = $comment->is_public;
            $reply->save();
        }
    }
}
