<?php

namespace Feeldee\Framework\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SetUserObserver
{
    /**
     * Handle the Model "creating" event.
     *
     * @param  \Feeldee\Framework\Models\Model  $model
     * @return void
     */
    public function creating(Model $model)
    {
        $id = Auth::id();
        $model->created_by = $id;
        $model->updated_by = $id;
    }

    /**
     * Handle the Model "updating" event.
     *
     * @param  \Feeldee\Framework\Models\Model  $model
     * @return void
     */
    public function updating(Model $model)
    {
        $model->updated_by = Auth::id();
    }
}
