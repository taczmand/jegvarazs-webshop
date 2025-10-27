<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class WatchedProduct extends Model
{
    use LogsActivity;
    protected $guarded = [];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }


}
