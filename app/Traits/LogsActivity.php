<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\ActionLogger;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        static::created(function (Model $model) {
            ActionLogger::log('created', $model, $model->getAttributes());
        });

        static::updated(function (Model $model) {
            ActionLogger::log('updated', $model, $model->getChanges());
        });

        static::deleted(function (Model $model) {
            ActionLogger::log('deleted', $model, $model->getOriginal());
        });
    }
}
