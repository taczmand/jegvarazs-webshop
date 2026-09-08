<?php

namespace App\Models;

use App\Observers\CartItemObserver;
use App\Services\Pricing\ProductGroupQuantityDiscountService;
use App\Services\Pricing\QuantityDiscountService;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use LogsActivity;

    protected $fillable = ['cart_id', 'product_id', 'quantity'];

    protected $appends = [
        'discounted_unit_gross_price',
        'discounted_unit_net_price',
        'discounted_row_gross_total',
        'discount_applied',
    ];

    protected static function booted()
    {
        static::observe(CartItemObserver::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getDiscountedUnitGrossPriceAttribute(): ?float
    {
        if (!$this->product) {
            return null;
        }

        $base = (float) $this->product->display_gross_price;
        $qty = (int) $this->quantity;

        $productDiscounted = app(QuantityDiscountService::class)
            ->discountedUnitGrossPrice($this->product, $qty, $base);

        $groupDiscounted = null;
        if ($this->cart_id) {
            $percent = app(ProductGroupQuantityDiscountService::class)
                ->percentForCart($this->product, (int) $this->cart_id);

            if ($percent > 0) {
                $groupDiscounted = (float) max(0, round($base * (1 - $percent / 100), 2));
            }
        }

        if ($groupDiscounted === null) {
            return (float) $productDiscounted;
        }

        return (float) min((float) $productDiscounted, (float) $groupDiscounted);
    }

    public function getDiscountedUnitNetPriceAttribute(): ?float
    {
        if (!$this->product) {
            return null;
        }

        $gross = $this->discounted_unit_gross_price;
        if ($gross === null) {
            return null;
        }

        $vat = (float) ($this->product->taxCategory?->tax_value ?? 0);
        return round((float) ($gross / (1 + $vat / 100)), 2);
    }

    public function getDiscountedRowGrossTotalAttribute(): ?float
    {
        $unit = $this->discounted_unit_gross_price;
        if ($unit === null) {
            return null;
        }
        return round((float) ($unit * (int) $this->quantity), 2);
    }

    public function getDiscountAppliedAttribute(): bool
    {
        if (!$this->product) {
            return false;
        }
        return round((float) $this->product->display_gross_price, 2) !== round((float) $this->discounted_unit_gross_price, 2);
    }
}
