<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use LogsActivity;
    protected $guarded = [];
}
