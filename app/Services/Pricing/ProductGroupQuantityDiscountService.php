<?php

namespace App\Services\Pricing;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductGroupQuantityDiscount;

class ProductGroupQuantityDiscountService
{
    public function percentForCart(Product $product, int $cartId): float
    {
        $groupIds = $product->productGroups()->pluck('product_groups.id')->all();
        if (count($groupIds) === 0) {
            return 0.0;
        }

        $rules = ProductGroupQuantityDiscount::query()
            ->whereIn('product_group_id', $groupIds)
            ->get();

        $bestPercent = 0.0;

        foreach ($rules as $rule) {
            if (!$this->isRuleActive($rule)) {
                continue;
            }

            $baseQty = (int) $rule->base_quantity;
            if ($baseQty < 1) {
                continue;
            }

            $totalQtyInGroup = (int) CartItem::query()
                ->where('cart_id', $cartId)
                ->where('cart_items.product_id', '<>', 1)
                ->join('product_group_product as pgp', 'cart_items.product_id', '=', 'pgp.product_id')
                ->where('pgp.product_group_id', (int) $rule->product_group_id)
                ->sum('cart_items.quantity');

            if ($totalQtyInGroup < $baseQty) {
                continue;
            }

            $steps = intdiv($totalQtyInGroup, $baseQty);
            $percent = (float) $steps * (float) $rule->percent_per_step;

            if ($rule->max_percent !== null) {
                $percent = min($percent, (float) $rule->max_percent);
            }

            $bestPercent = max($bestPercent, (float) round($percent, 2));
        }

        return max(0.0, $bestPercent);
    }

    public function nextStepHintForCart(Product $product, int $cartId): ?array
    {
        $groupIds = $product->productGroups()->pluck('product_groups.id')->all();
        if (count($groupIds) === 0) {
            return null;
        }

        $rules = ProductGroupQuantityDiscount::query()
            ->whereIn('product_group_id', $groupIds)
            ->get();

        $best = null;

        foreach ($rules as $rule) {
            if (!$this->isRuleActive($rule)) {
                continue;
            }

            $baseQty = (int) $rule->base_quantity;
            if ($baseQty < 1) {
                continue;
            }

            $totalQtyInGroup = (int) CartItem::query()
                ->where('cart_id', $cartId)
                ->where('cart_items.product_id', '<>', 1)
                ->join('product_group_product as pgp', 'cart_items.product_id', '=', 'pgp.product_id')
                ->where('pgp.product_group_id', (int) $rule->product_group_id)
                ->sum('cart_items.quantity');

            $steps = $totalQtyInGroup > 0 ? intdiv($totalQtyInGroup, $baseQty) : 0;
            $currentPercent = (float) $steps * (float) $rule->percent_per_step;
            if ($rule->max_percent !== null) {
                $currentPercent = min($currentPercent, (float) $rule->max_percent);
            }
            $currentPercent = (float) round($currentPercent, 2);

            $nextSteps = $steps + 1;

            $nextTotalQty = $nextSteps * $baseQty;
            $needMore = max(0, $nextTotalQty - $totalQtyInGroup);
            if ($needMore === 0) {
                continue;
            }

            $nextPercent = (float) $nextSteps * (float) $rule->percent_per_step;
            if ($rule->max_percent !== null) {
                $nextPercent = min($nextPercent, (float) $rule->max_percent);
            }
            $nextPercent = (float) round($nextPercent, 2);
            if ($nextPercent <= $currentPercent) {
                continue;
            }
            if ($nextPercent <= 0) {
                continue;
            }

            $candidate = [
                'product_group_id' => (int) $rule->product_group_id,
                'total_qty_in_group' => (int) $totalQtyInGroup,
                'next_total_qty' => (int) $nextTotalQty,
                'need_more' => (int) $needMore,
                'next_percent' => (float) $nextPercent,
            ];

            if ($best === null) {
                $best = $candidate;
                continue;
            }

            if ($candidate['next_percent'] > $best['next_percent']) {
                $best = $candidate;
                continue;
            }

            if ($candidate['next_percent'] === $best['next_percent'] && $candidate['need_more'] < $best['need_more']) {
                $best = $candidate;
            }
        }

        return $best;
    }

    private function isRuleActive(ProductGroupQuantityDiscount $rule): bool
    {
        if (!(bool) $rule->is_active) {
            return false;
        }

        $now = now();

        if ($rule->starts_at && $rule->starts_at->gt($now)) {
            return false;
        }

        if ($rule->ends_at && $rule->ends_at->lt($now)) {
            return false;
        }

        return true;
    }
}
