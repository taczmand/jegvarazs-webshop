<?php

namespace App\Services\Pricing;

use App\Models\Product;
use App\Models\ProductQuantityDiscount;
use Illuminate\Support\Collection;

class QuantityDiscountService
{
    public function discountedUnitGrossPrice(Product $product, int $quantity, float $baseUnitGrossPrice): float
    {
        $rule = $this->getApplicableRule($product, $quantity);
        if (!$rule) {
            return (float) $baseUnitGrossPrice;
        }

        $discounted = match ($rule->discount_type) {
            'percent' => $baseUnitGrossPrice * (1 - ((float) $rule->discount_value / 100)),
            'fixed' => $baseUnitGrossPrice - (float) $rule->discount_value,
            default => $baseUnitGrossPrice,
        };

        return (float) max(0, round($discounted, 2));
    }

    public function getApplicableRule(Product $product, int $quantity): ?ProductQuantityDiscount
    {
        if ($quantity < 1) {
            return null;
        }

        $rules = $this->getActiveRulesForProduct($product);

        return $rules
            ->where('min_quantity', '<=', $quantity)
            ->sortByDesc('min_quantity')
            ->first();
    }

    /**
     * @return Collection<int, ProductQuantityDiscount>
     */
    private function getActiveRulesForProduct(Product $product): Collection
    {
        $now = now();

        if ($product->relationLoaded('quantityDiscounts')) {
            $query = $product->quantityDiscounts->toBase();
        } else {
            $query = $product->quantityDiscounts()->get()->toBase();
        }

        return $query
            ->filter(fn (ProductQuantityDiscount $r) => (bool) $r->is_active)
            ->filter(fn (ProductQuantityDiscount $r) => !$r->starts_at || $r->starts_at->lte($now))
            ->filter(fn (ProductQuantityDiscount $r) => !$r->ends_at || $r->ends_at->gte($now))
            ->values();
    }
}
