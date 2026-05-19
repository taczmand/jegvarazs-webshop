<?php

namespace App\Services\Search;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProductSearchService
{
    private array $synonyms = [
        'klima' => ['klima', 'klima berendezes', 'klimaberendezes', 'klimaberendezés', 'klíma', 'klímaberendezés', 'klímaberendezes'],
        'klíma' => ['klíma', 'klima', 'klimaberendezes', 'klímaberendezes', 'klimaberendezés', 'klímaberendezés'],
        'multi' => ['multi', 'multiklima', 'multiklíma', 'multi split', 'multisplit'],
        'multiklima' => ['multiklima', 'multiklíma', 'multi'],
        'multiklíma' => ['multiklíma', 'multiklima', 'multi'],
        'hőszivattyú' => ['hőszivattyú', 'hoszivattyu', 'hőszivattyu', 'hoszivattyú'],
        'hoszivattyu' => ['hoszivattyu', 'hőszivattyú'],
        'ft' => ['ft', 'forint'],
    ];

    public function __construct(
        private readonly TextNormalizer $normalizer,
    ) {
    }

    /**
     * @param array<int, string> $must
     * @param array<int, string> $should
     */
    public function apply(Builder $query, array $must = [], array $should = [], ?string $rawQuery = null): Builder
    {
        $driver = DB::connection()->getDriverName();

        $mustTokens = $this->expandTokens($must);
        $shouldTokens = $this->expandTokens($should);

        if (($mustTokens === []) && ($shouldTokens === []) && is_string($rawQuery)) {
            $shouldTokens = $this->expandTokens($this->normalizer->tokens($rawQuery));
        }

        if ($driver !== 'mysql') {
            return $this->applyLikeFallback($query, array_values(array_unique(array_merge($mustTokens, $shouldTokens))));
        }

        $boolean = $this->buildBooleanQuery($mustTokens, $shouldTokens);
        if ($boolean === '') {
            return $query;
        }

        $query->whereRaw('MATCH(search_text) AGAINST (? IN BOOLEAN MODE)', [$boolean]);
        $query->orderByRaw('MATCH(search_text) AGAINST (? IN BOOLEAN MODE) DESC', [$boolean]);

        return $query;
    }

    /**
     * @param array<int, string> $tokens
     * @return array<int, string>
     */
    private function expandTokens(array $tokens): array
    {
        $out = [];

        foreach ($tokens as $t) {
            $t = trim((string) $t);
            if ($t === '') {
                continue;
            }

            $norm = $this->normalizer->normalize($t);
            if ($norm !== '') {
                $out[] = $norm;
            }

            $key = $norm;
            if ($key !== '' && isset($this->synonyms[$key])) {
                foreach ($this->synonyms[$key] as $syn) {
                    $synNorm = $this->normalizer->normalize((string) $syn);
                    if ($synNorm !== '') {
                        $out[] = $synNorm;
                    }
                }
            }
        }

        return array_values(array_unique(array_filter($out, fn ($v) => $v !== '')));
    }

    /**
     * @param array<int, string> $must
     * @param array<int, string> $should
     */
    private function buildBooleanQuery(array $must, array $should): string
    {
        $chunks = [];

        foreach ($must as $t) {
            $variants = $this->tokenVariants($t);
            if ($variants === []) {
                continue;
            }
            $chunks[] = '+(' . implode(' ', array_map(fn ($v) => $this->escapeBooleanToken($v) . '*', $variants)) . ')';
        }

        foreach ($should as $t) {
            $variants = $this->tokenVariants($t);
            if ($variants === []) {
                continue;
            }
            $chunks[] = '(' . implode(' ', array_map(fn ($v) => $this->escapeBooleanToken($v) . '*', $variants)) . ')';
        }

        return trim(implode(' ', $chunks));
    }

    /**
     * @return array<int, string>
     */
    private function tokenVariants(string $token): array
    {
        $token = trim($token);
        if ($token === '') {
            return [];
        }

        $variants = [$token];

        if (preg_match('/\d[\.,]\d/u', $token)) {
            $variants[] = str_replace('.', ',', $token);
            $variants[] = str_replace(',', '.', $token);
        }

        return array_values(array_unique(array_filter(array_map(fn ($v) => trim((string) $v), $variants), fn ($v) => $v !== '')));
    }

    private function escapeBooleanToken(string $token): string
    {
        $token = preg_replace('/[+\-<>()~*\"@]/u', ' ', $token) ?? $token;
        $token = preg_replace('/\s+/u', ' ', $token) ?? $token;
        return trim($token);
    }

    /**
     * @param array<int, string> $tokens
     */
    private function applyLikeFallback(Builder $query, array $tokens): Builder
    {
        if ($tokens === []) {
            return $query;
        }

        foreach ($tokens as $t) {
            $t = trim((string) $t);
            if ($t === '') {
                continue;
            }

            $query->where('search_text', 'like', '%' . $t . '%');
        }

        return $query;
    }

    /**
     * @param array<int, Product> $products
     * @return array<int, Product>
     */
    public function fuzzyRerank(array $products, string $query, int $limit = 100): array
    {
        $q = $this->normalizer->normalize($query);
        if ($q === '' || $products === []) {
            return $products;
        }

        $scored = [];
        foreach ($products as $p) {
            $title = $this->normalizer->normalize((string) ($p->title ?? ''));
            $dist = levenshtein($q, $title);
            $scored[] = ['p' => $p, 'd' => $dist];
        }

        usort($scored, fn ($a, $b) => $a['d'] <=> $b['d']);

        return array_values(array_map(fn ($x) => $x['p'], array_slice($scored, 0, $limit)));
    }
}
