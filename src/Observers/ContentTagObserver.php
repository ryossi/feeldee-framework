<?php

namespace Feeldee\Framework\Observers;

use Feeldee\Framework\Models\Content;

class ContentTagObserver
{
    /**
     * Handle the Content "deleting" event.
     *
     * @param  \Feeldee\Framework\Models\Content  $content
     * @return void
     */
    public function deleting(Content $content)
    {
        // タグ付け削除
        $content->tags()->detach();
    }
}
