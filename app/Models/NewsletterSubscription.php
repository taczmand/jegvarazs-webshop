<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscription extends Model
{
    use LogsActivity;
    protected $fillable = [
        'email'
    ];
}
