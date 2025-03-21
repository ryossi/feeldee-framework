<?php

namespace Feeldee\Framework\Observers;

use Feeldee\Framework\Facades\Tracking;
use Illuminate\Database\Eloquent\Model;

class SetUidObserver
{
    /**
     * Handle the Model "creating" event.
     *
     * @param  \App\Models\Model  $model
     * @return void
     */
    public function creating(Model $model)
    {
        $model->uid = Tracking::uid();
    }
}
