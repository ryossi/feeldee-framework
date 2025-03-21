<?php

namespace Feeldee\Framework\Observers;

use Feeldee\Framework\Models\Post;

class PostPhotoSyncObserver
{
    /**
     * Handle the Post "updated" event.
     *
     * @param  \App\Models\Post  $post
     * @return void
     */
    public function updated(Post $post)
    {
        // 投稿写真リストシンクロ
        $post->syncPhotos($post);
    }

    /**
     * Handle the Post "deleting" event.
     *
     * @param  \App\Models\Post  $post
     * @return void
     */
    public function deleting(Post $post)
    {
        // 投稿写真リスト削除
        $post->photos()->delete();
    }
}
