<?php

namespace Feeldee\Framework\Observers;

use Feeldee\Framework\Models\Point;

class PointObserver
{
    /**
     * Handle the Point "creating" event.
     *
     * @param  \Feeldee\Framework\Models\Point  $point
     * @return void
     */
    public function creating(Point $point)
    {
        $point->profile = $point->post->profile;
    }
}
