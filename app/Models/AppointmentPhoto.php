<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class AppointmentPhoto extends Model
{
    use LogsActivity;

    protected $guarded = [];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
