<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class BasicData extends Model
{
    use LogsActivity;
    protected $fillable = ['key', 'value'];

}
