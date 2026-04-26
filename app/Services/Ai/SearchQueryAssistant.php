<?php

namespace App\Services\Ai;

class SearchQueryAssistant
{
    public function __construct(
        private readonly OpenAiClient $client,
    ) {
    }

    public function enrich(string $query, array $context = []): array
    {
        $query = trim($query);
        if ($query === '') {
            return [
                'original_query' => '',
                'rewritten_query' => '',
                'keywords' => [],
                'category' => null,
            ];
        }

        $openAiEnabled = (bool) config('services.openai.enabled', true);
        $openAiKey = (string) config('services.openai.key', '');
        if (!$openAiEnabled || $openAiKey === '') {
            return [
                'original_query' => $query,
                'rewritten_query' => $query,
                'keywords' => $this->fallbackKeywords($query),
                'category' => null,
            ];
        }

        // Rövid, 1 szavas keresések (pl. márkanév / cikkszám) esetén ne bővítsünk AI-val,
        // mert túl sok irreleváns kulcsszót adhat hozzá.
        $tokens = preg_split('/\s+/u', $query) ?: [];
        $tokens = array_values(array_filter(array_map('trim', $tokens), fn ($t) => $t !== ''));
        $isSingleToken = count($tokens) === 1;
        $len = mb_strlen($query);
        $looksLikeCodeOrBrand = (bool) preg_match('/^[a-z0-9\-_.]+$/iu', $query);
        $containsDigit = (bool) preg_match('/\d/u', $query);

        if ($isSingleToken && $looksLikeCodeOrBrand && ($len <= 4 || $containsDigit)) {
            return [
                'original_query' => $query,
                'rewritten_query' => $query,
                'keywords' => [$query],
                'category' => null,
            ];
        }

        $categories = $context['categories'] ?? [];
        if (!is_array($categories)) {
            $categories = [];
        }

        $system = [
            'role' => 'system',
            'content' => 'You rewrite Hungarian webshop search queries into a better query and keyword list for SQL LIKE search. Return JSON only.'
        ];

        $user = [
            'role' => 'user',
            'content' => json_encode([
                'query' => $query,
                'known_categories' => array_values(array_map('strval', $categories)),
                'task' => 'Rewrite the query to be more searchable, fix typos, add synonyms, and optionally guess a category from known_categories. Return: rewritten_query (string), keywords (array of strings, 3-10 items, no duplicates), category (string|null, must be from known_categories), language (string).'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];

        $data = $this->client->chatJson([$system, $user], [
            'temperature' => 0.2,
        ]);

        \Log::info('SearchQueryAssistant', ['data' => $data]);

        if (!is_array($data)) {
            return [
                'original_query' => $query,
                'rewritten_query' => $query,
                'keywords' => $this->fallbackKeywords($query),
                'category' => null,
            ];
        }

        $rewritten = isset($data['rewritten_query']) && is_string($data['rewritten_query'])
            ? trim($data['rewritten_query'])
            : $query;

        $keywords = [];
        if (isset($data['keywords']) && is_array($data['keywords'])) {
            foreach ($data['keywords'] as $kw) {
                if (!is_string($kw)) {
                    continue;
                }
                $kw = trim($kw);
                if ($kw === '') {
                    continue;
                }
                $keywords[] = $kw;
            }
        }

        $keywords = array_values(array_unique($keywords));
        if ($keywords === []) {
            $keywords = $this->fallbackKeywords($rewritten);
        }

        $category = null;
        if (isset($data['category']) && is_string($data['category'])) {
            $cand = trim($data['category']);
            if ($cand !== '' && in_array($cand, $categories, true)) {
                $category = $cand;
            }
        }

        return [
            'original_query' => $query,
            'rewritten_query' => $rewritten !== '' ? $rewritten : $query,
            'keywords' => array_slice($keywords, 0, 10),
            'category' => $category,
        ];
    }

    private function fallbackKeywords(string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $parts = preg_split('/\s+/u', $query) ?: [];
        $parts = array_values(array_filter(array_map('trim', $parts), fn ($p) => $p !== ''));

        return array_slice(array_values(array_unique($parts)), 0, 10);
    }
}
