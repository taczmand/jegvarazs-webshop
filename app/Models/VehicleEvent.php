<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class VehicleEvent extends Model
{
    use LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
