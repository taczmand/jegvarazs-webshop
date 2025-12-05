<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'email_template',
        'email_address',
        'subject',
        'body',
        'status',
        'sent_at',
        'error_message',
    ];
}
