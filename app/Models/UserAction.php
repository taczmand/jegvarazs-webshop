<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAction extends Model
{
    protected $fillable = ['user_id', 'action', 'model', 'model_id', 'data'];
}
