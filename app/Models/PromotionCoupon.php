<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class PromotionCoupon extends Model
{
    use LogsActivity;

    protected $fillable = [
        'promotion_id',
        'code',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function usages()
    {
        return $this->hasMany(PromotionCouponUsage::class);
    }
}
