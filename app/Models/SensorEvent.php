<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorEvent extends Model
{
    protected $table = 'sensor_events';

    protected $guarded = [];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];
}
