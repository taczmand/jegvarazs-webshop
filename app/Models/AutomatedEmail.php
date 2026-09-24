<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class AutomatedEmail extends Model
{
    use LogsActivity;

    protected $casts = [
        'payload' => 'array',
    ];

    protected $fillable = [
        'email_id',
        'email_address',
        'email_template',
        'full_name',
        'phone',
        'address',
        'zip',
        'city',
        'payload',
        'frequency_unit',
        'frequency_interval',
        'send_at',
        'last_sent_at',
    ];
}
