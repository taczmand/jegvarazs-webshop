<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Download extends Model
{
    use LogsActivity;

    protected $fillable = ['file_name', 'file_path', 'file_description', 'status'];
}
