<?php

namespace App\Services\Search\Engines;

use Illuminate\Database\Eloquent\Builder;

class NormalProductSearchEngine extends AbstractProductSearchEngine
{
    protected function applyQuery(Builder $builder, string $query): Builder
    {
        $tokens = $this->normalizer->tokens($query);

        if ($tokens === []) {
            return $builder;
        }

        foreach ($tokens as $t) {
            $builder->where(function (Builder $q) use ($t) {
                $like = '%' . $t . '%';

                $q->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('search_text', 'like', $like);
            });
        }

        $scoreParts = [];
        $scoreBindings = [];
        foreach ($tokens as $t) {
            $like = '%' . $t . '%';
            $scoreParts[] = "(CASE WHEN title LIKE ? THEN 3 WHEN description LIKE ? THEN 2 WHEN search_text LIKE ? THEN 1 ELSE 0 END)";
            $scoreBindings[] = $like;
            $scoreBindings[] = $like;
            $scoreBindings[] = $like;
        }
        $scoreSql = implode(' + ', $scoreParts);

        $builder
            ->orderByRaw('(' . $scoreSql . ') DESC', $scoreBindings)
            ->orderBy('title');

        return $builder;
    }
}
