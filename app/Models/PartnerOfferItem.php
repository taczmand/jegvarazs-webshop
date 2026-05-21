<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerOfferItem extends Model
{
    protected $guarded = [];

    public function partnerOffer(): BelongsTo
    {
        return $this->belongsTo(PartnerOffer::class, 'partner_offer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
