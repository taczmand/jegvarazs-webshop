<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'vehicle_user')->withTimestamps();
    }
}
