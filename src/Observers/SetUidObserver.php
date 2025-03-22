<?php

namespace Feeldee\Framework\Observers;

use Feeldee\Framework\Facades\Tracking;
use Illuminate\Database\Eloquent\Model;

class SetUidObserver
{
    /**
     * Handle the Model "creating" event.
     *
     * @param  \Feeldee\Framework\Models\Model  $model
     * @return void
     */
    public function creating(Model $model)
    {
        $model->uid = Tracking::uid();
    }
}
