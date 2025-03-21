<?php

namespace Feeldee\Framework\Observers;

use Feeldee\Framework\Models\Content;

class ContentRecordObserver
{
    /**
     * Handle the Content "deleting" event.
     *
     * @param  \App\Models\Content  $content
     * @return void
     */
    public function deleting(Content $content)
    {
        // レコード削除
        $content->records()->delete();
    }
}
