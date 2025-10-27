<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use LogsActivity;
    protected $fillable = [
        'name',
        'email',
        'phone',
        'position',
        'profile_photo_path',
    ];
}
