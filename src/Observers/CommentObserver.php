<?php

namespace Feeldee\Framework\Observers;

use Feeldee\Framework\Models\Comment;

class CommentObserver
{
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
