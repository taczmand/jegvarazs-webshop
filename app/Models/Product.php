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
    public function brands()
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

    public function getDisplayNetPriceAttribute()
    {
        $customer = auth('customer')->user();

        // ÁFA kulcs lekérése (pl. 27, ha 27%)
        $vat_percent = $this->taxCategory->tax_value ?? 0;

        if (!$customer) {
            return round((float)$this->gross_price / (1 + $vat_percent / 100), 2);
        }

        $is_partner = $customer->is_partner;

        if ($is_partner) {
            $partner_gross_price = $this->partner_selected_price ?? $this->partner_gross_price;
            $gross_price = $partner_gross_price ?? $this->gross_price;
        } else {
            $gross_price = $this->gross_price;
        }

        $net_price = $gross_price / (1 + $vat_percent / 100);

        return round((float)$net_price, 2);
    }

    public function getDisplayAllPricesAttribute()
    {
        $customer = auth('customer')->user();

        $vat_percent = $this->taxCategory->tax_value ?? 0;

        $output = '';

        $gross_price = (float) $this->gross_price;

        // Nettó ár számítása (feltételezve, hogy van ilyen meződ)
        /*if ($this->net_price) {
            $output .= '<div><strong>Nettó ár:</strong> ' . number_format($this->net_price, 0, ',', ' ') . ' Ft</div>';
        }*/

        // Ha partner, és van beállított partner ár
        if ($customer && $customer->is_partner) {
            $partner_price = $this->partner_selected_price ?? $this->partner_gross_price;

            if ($partner_price !== null && $gross_price != $partner_price) {

                // Nettó partneri ár:
                $partner_net_price = $partner_price / (1 + $vat_percent / 100);
                $output .= '<div style="color: red; font-size: 1.75rem"><strong>' . number_format($partner_net_price, 0, ',', ' ') . ' Ft</strong> <span style="font-size: 10px">(partner nettó ár)</span></div>';

                $partner_price = (float) $partner_price;
                $output .= '<div><strong>' . number_format($partner_price, 0, ',', ' ') . ' Ft</strong> <span style="font-size: 10px">(partner bruttó ár)</span></div>';


            }
        }

        // Végfelhasználói bruttó ár
        $output .= '<div class="price-block">';
        $output .= '<div><strong>'.number_format($gross_price, 0, ',', ' ') . ' Ft </strong> <span style="font-size: 10px;">(bruttó végfelhasználói ár)</span></div>';


        $output .= '</div>';

        return $output;
    }


}
