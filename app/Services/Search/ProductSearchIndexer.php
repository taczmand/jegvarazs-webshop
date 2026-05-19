<?php

namespace App\Services\Search;

use App\Models\Product;

class ProductSearchIndexer
{
    public function __construct(
        private readonly TextNormalizer $normalizer,
    ) {
    }

    public function rebuild(Product $product): void
    {
        $product->loadMissing(['category', 'brands', 'tags', 'attributes']);

        $parts = [];

        $parts[] = (string) ($product->title ?? '');
        $parts[] = (string) ($product->slug ?? '');
        $parts[] = (string) ($product->description ?? '');

        $parts[] = (string) ($product->category?->title ?? '');
        $parts[] = (string) ($product->brands?->title ?? '');

        foreach ($product->tags ?? [] as $tag) {
            $parts[] = (string) ($tag->name ?? '');
        }

        foreach ($product->attributes ?? [] as $attr) {
            $parts[] = (string) ($attr->name ?? '');
            $pivotValue = $attr->pivot?->value ?? null;
            if ($pivotValue !== null) {
                $parts[] = (string) $pivotValue;
            }
        }

        $raw = trim(implode("\n", array_values(array_filter($parts, fn ($p) => trim((string) $p) !== ''))));
        $normalized = $this->normalizer->normalize($raw);

        $combined = trim($raw . "\n" . $normalized);

        $product->forceFill([
            'search_text' => $combined,
        ])->saveQuietly();
    }
}
