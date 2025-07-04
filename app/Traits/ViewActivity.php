<?php

namespace App\Traits;

use Viewer;
use Illuminate\Database\Eloquent\Model;

trait ViewActivity
{
    public static function bootLogsActivity()
    {
        static::created(function (Model $model) {
            Viewer::add('created', $model, $model->getAttributes());
        });
    }
}
