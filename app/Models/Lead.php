<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'lead_id',
        'form_id',
        'form_name',
        'full_name',
        'email',
        'phone',
        'city',
        'campaign_name',
        'status',
        'viewed_by',
        'viewed_at',
        'data'
    ];
}
