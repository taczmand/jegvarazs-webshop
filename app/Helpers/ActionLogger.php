<?php

namespace App\Helpers;

use App\Models\UserAction;

class ActionLogger
{
    public static function log(string $action, $model = null, array $data = [])
    {
        UserAction::create([
            'user_id' => auth('admin')->id(),
            'action' => $action,
            'model' => is_object($model) ? $model->getTable() : $model,
            'model_id' => is_object($model) && isset($model->id) ? $model->id : null,
            'data' => !empty($data) ? json_encode($data) : null,
        ]);
    }
}
