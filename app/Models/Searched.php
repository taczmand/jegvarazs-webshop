<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Searched extends Model
{
    use LogsActivity;
    protected $table = 'searched';
    protected $guarded = [];

}
