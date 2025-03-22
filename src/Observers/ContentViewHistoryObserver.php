<?php

namespace Feeldee\Framework\Observers;

use Feeldee\Framework\Models\ContentViewHistory;

class ContentViewHistoryObserver
{
    /**
     * Handle the Comment "creating" event.
     *
     * @param  \Feeldee\Framework\Models\ContentViewHistory  $contentViewHistory
     * @return void
     */
    public function creating(ContentViewHistory $contentViewHistory)
    {
        $contentViewHistory->profile = $contentViewHistory->content->profile;
    }
}
