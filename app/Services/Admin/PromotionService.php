<?php

namespace App\Services\Admin;

use App\Models\Promotion;
use App\Models\PromotionCoupon;
use Exception;
use Illuminate\Support\Str;

class PromotionService
{
    public function store(array $data): array
    {
        try {
            $promotion = Promotion::create($this->normalizeData($data));

            return ['success' => true, 'data' => $promotion, 'error' => null];
        } catch (Exception $e) {
            return ['success' => false, 'data' => null, 'error' => $e->getMessage()];
        }
    }

    public function update(Promotion $promotion, array $data): array
    {
        try {
            $promotion->update($this->normalizeData($data, $promotion->id));
            return ['success' => true, 'data' => $promotion, 'error' => null];
        } catch (Exception $e) {
            return ['success' => false, 'data' => null, 'error' => $e->getMessage()];
        }
    }

    public function delete(Promotion $promotion): array
    {
        try {
            $promotion->delete();
            return ['success' => true, 'error' => null];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function generateCoupons(Promotion $promotion, int $quantity, ?string $prefix = null): array
    {
        try {
            $prefix = $prefix !== null ? trim($prefix) : '';
            $created = [];

            for ($i = 0; $i < $quantity; $i++) {
                do {
                    $code = strtoupper($prefix . Str::random(8));
                } while (PromotionCoupon::where('code', $code)->exists());

                $created[] = PromotionCoupon::create([
                    'promotion_id' => $promotion->id,
                    'code' => $code,
                ]);
            }

            return ['success' => true, 'data' => $created, 'error' => null];
        } catch (Exception $e) {
            return ['success' => false, 'data' => null, 'error' => $e->getMessage()];
        }
    }

    private function normalizeData(array $data, ?int $excludeId = null): array
    {
        $data['active'] = array_key_exists('active', $data) ? (bool) $data['active'] : false;
        $data['slug'] = $this->resolveSlug($data['slug'] ?? null, $data['name'] ?? '', $excludeId);
        $data['discount_percent'] = ($data['discount_percent'] ?? '') === '' ? null : $data['discount_percent'];
        $data['starts_at'] = ($data['starts_at'] ?? '') === '' ? null : $data['starts_at'];
        $data['ends_at'] = ($data['ends_at'] ?? '') === '' ? null : $data['ends_at'];
        $data['description'] = ($data['description'] ?? '') === '' ? null : $data['description'];

        return $data;
    }

    private function resolveSlug(?string $slug, string $name, ?int $excludeId = null): string
    {
        $slug = $slug !== null && trim($slug) !== '' ? Str::slug($slug) : Str::slug($name);

        if ($slug === '') {
            $slug = 'promocio';
        }

        $base = $slug;
        $i = 2;
        while (
            Promotion::where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
