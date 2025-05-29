<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use LogsActivity;

    protected $fillable = ['name'];
}
