<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use LogsActivity;

    protected $guarded = [];
    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'product_attributes')
            ->withPivot('value')
            ->withTimestamps();
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'product_tags');
    }

    public function photos()
    {
        return $this->hasMany(ProductPhoto::class);
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }
    public function taxCategory()
    {
        return $this->belongsTo(TaxCategory::class, 'tax_id');
    }
    public function netPrice()
    {
        //return $this->price / (1 + $this->taxCategory->tax_value / 100);

    }
    public function scopeActive()
    {
        return $this->where('status', 'active');
    }

    public function partnerProducts()
    {
        return $this->hasMany(PartnerProduct::class, 'product_id');
    }
    public function offerProducts()
    {
        return $this->belongsToMany(Offer::class, 'offer_products')
            ->withPivot('gross_price')
            ->withTimestamps();
    }

    public function getNetPriceAttribute()
    {
        return $this->gross_price / (1 + $this->taxCategory->tax_value / 100);
    }

    public function getNetPartnerPriceAttribute()
    {
        return $this->partner_gross_price / (1 + $this->taxCategory->tax_value / 100);
    }

    public function getPartnerSelectedPriceAttribute()
    {
        return $this->partnerProducts->first()->discount_gross_price ?? null;
    }
    public function getDisplayGrossPriceAttribute()
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            return (float)$this->gross_price;
        }
        $is_partner = $customer->is_partner;
        if ($is_partner) {
            $partner_gross_price = $this->partner_selected_price ?? $this->partner_gross_price ;
            $gross_price = $partner_gross_price ?? $this->gross_price;
        } else {
            $gross_price = $this->gross_price;
        }
        return (float)$gross_price;
    }

}
