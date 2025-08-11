<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorksheetWorker extends Model
{
    protected $guarded = [];

    public function worksheet()
    {
        return $this->belongsTo(Worksheet::class, 'worksheet_id');
    }
    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }
}
