<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\ActionLogger;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        static::created(function (Model $model) {
            ActionLogger::log('created', $model, [
                'new' => $model->getAttributes(),
            ]);
        });

        static::updated(function (Model $model) {
            $changes = $model->getChanges();
            $original = array_intersect_key($model->getOriginal(), $changes);

            ActionLogger::log('updated', $model, [
                'old' => $original,
                'new' => $changes,
            ]);
        });

        static::deleted(function (Model $model) {
            ActionLogger::log('deleted', $model, [
                'old' => $model->getOriginal(),
            ]);
        });
    }
}
