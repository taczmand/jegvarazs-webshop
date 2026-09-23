<?php

namespace App\Services\Search;

use App\Services\Search\Engines\NormalProductSearchEngine;
use App\Services\Search\Engines\ProductSearchEngine;
use Illuminate\Database\Eloquent\Builder;

class ProductSearchService
{
    public function __construct(
        private readonly TextNormalizer $normalizer,
    ) {
    }

    public function query(string $query, ?string $engine = null): Builder
    {
        $engine = $engine !== null ? trim($engine) : '';
        if ($engine === '') {
            $engine = (string) config('services.product_search.engine', 'normal');
        }

        return $this->resolveEngine($engine)->query($query);
    }

    private function resolveEngine(string $engine): ProductSearchEngine
    {
        $engine = strtolower(trim($engine));

        return match ($engine) {
            'normal' => new NormalProductSearchEngine($this->normalizer),
            default => new NormalProductSearchEngine($this->normalizer),
        };
    }
}
