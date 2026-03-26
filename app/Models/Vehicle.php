<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'technical_inspection_expires_at' => 'date',
    ];

    public function events()
    {
        return $this->hasMany(VehicleEvent::class);
    }
}
