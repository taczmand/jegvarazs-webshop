<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutomatedEmail extends Model
{
    protected $fillable = [
        'email_id',
        'email_address',
        'email_template',
        'full_name',
        'phone',
        'address',
        'zip',
        'city',
        'frequency_unit',
        'frequency_interval',
        'last_sent_at',
    ];
}
