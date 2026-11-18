<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionCouponUsage extends Model
{
    protected $fillable = [
        'promotion_coupon_id',
        'email',
        'sent_at',
        'used_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function coupon()
    {
        return $this->belongsTo(PromotionCoupon::class, 'promotion_coupon_id');
    }
}
