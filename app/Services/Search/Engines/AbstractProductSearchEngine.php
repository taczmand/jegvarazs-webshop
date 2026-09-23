<?php

namespace App\Services\Search\Engines;

use App\Models\Product;
use App\Services\Search\TextNormalizer;
use Illuminate\Database\Eloquent\Builder;

abstract class AbstractProductSearchEngine implements ProductSearchEngine
{
    public function __construct(
        protected readonly TextNormalizer $normalizer,
    ) {
    }

    public function query(string $query): Builder
    {
        $query = trim($query);

        $builder = Product::query()
            ->where('status', 'active');

        if ($query === '') {
            return $builder;
        }

        return $this->applyQuery($builder, $query);
    }

    abstract protected function applyQuery(Builder $builder, string $query): Builder;
}
