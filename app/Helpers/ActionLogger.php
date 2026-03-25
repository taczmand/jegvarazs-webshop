<?php

namespace App\Helpers;

use App\Models\UserAction;

class ActionLogger
{
    public static function log(string $action, $model = null, array $data = [])
    {
        if (is_object($model)) {
            $id = $model->id ?? null;

            $label = null;
            foreach (['title', 'name', 'email', 'subject', 'code'] as $key) {
                if (isset($model->{$key}) && $model->{$key} !== null && $model->{$key} !== '') {
                    $label = (string) $model->{$key};
                    break;
                }
            }

            if (is_null($label)) {
                $first = $model->first_name ?? null;
                $last = $model->last_name ?? null;
                if (!empty($first) || !empty($last)) {
                    $label = trim(((string) $last) . ' ' . ((string) $first));
                }
            }

            if (!isset($data['_record'])) {
                $data['_record'] = [
                    'id' => $id,
                    'label' => $label,
                ];
            }
        }

        UserAction::create([
            'user_id' => auth('admin')->id(),
            'action' => $action,
            'model' => is_object($model) ? $model->getTable() : $model,
            'model_id' => is_object($model) && isset($model->id) ? $model->id : null,
            'data' => !empty($data) ? $data : null,
        ]);
    }
}
