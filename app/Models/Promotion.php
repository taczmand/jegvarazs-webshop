<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'starts_at',
        'ends_at',
        'discount_percent',
        'active',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'discount_percent' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function coupons()
    {
        return $this->hasMany(PromotionCoupon::class);
    }
}
