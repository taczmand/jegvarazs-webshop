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
                'brand' => null,
                'attribute_filters' => [],
                'must' => [],
                'should' => [],
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
                'brand' => null,
                'attribute_filters' => [],
                'must' => $this->fallbackKeywords($query),
                'should' => [],
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
                'brand' => $query,
                'attribute_filters' => [],
                'must' => [$query],
                'should' => [],
            ];
        }

        $categories = $context['categories'] ?? [];
        if (!is_array($categories)) {
            $categories = [];
        }

        $system = [
            'role' => 'system',
            'content' => 'You help build a structured search plan for a Hungarian webshop. Return JSON only.'
        ];

        $user = [
            'role' => 'user',
            'content' => json_encode([
                'query' => $query,
                'known_categories' => array_values(array_map('strval', $categories)),
                'search_schema' => [
                    'products' => ['title', 'description', 'status', 'cat_id', 'brand_id'],
                    'brands' => ['title'],
                    'categories' => ['title'],
                    'tags' => ['name'],
                    'attributes' => ['name'],
                    'product_attributes_pivot' => ['value'],
                ],
                'rules' => [
                    'Prefer precision over recall when a brand is present (e.g. "AUX klíma" should require AUX).',
                    'Return must keywords that MUST match, and should keywords that are optional hints.',
                    'MUST and SHOULD items should be ATOMIC tokens. Avoid multi-word phrases. Split values like "3.2 kW" into separate tokens: "3.2" and "kW".',
                    'If the query contains decimal numbers, keep them as a separate token (e.g. "3.2"), do not combine with units or other words.',
                    'If the user intent is air conditioning and you include "klíma"/"klima", also include the webshop term "klímaberendezés"/"klimaberendezés" as an additional token (do not remove the original).',
                    'If the user intent is air conditioning, also consider multi-split / multi air conditioner terms. Add "multiklíma"/"multiklima" (and optionally "multi split") as SHOULD tokens when relevant, so a query like "AUX klíma" can match AUX multiklímák too.',
                    'If you output category, it must be exactly one of known_categories or null.',
                    'If you output brand, it should be a short brand string from the query when applicable.',
                    'attribute_filters should be array of {name, value|null} (value can be null if only attribute is mentioned).',
                    'Keep must and should arrays small (0-6). Avoid generic expansions that broaden too much.',
                ],
                'output_format' => [
                    'rewritten_query' => 'string',
                    'language' => 'string',
                    'brand' => 'string|null',
                    'category' => 'string|null',
                    'must' => 'array<string>',
                    'should' => 'array<string>',
                    'attribute_filters' => 'array<{name:string,value:string|null}>',
                ],
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
                'brand' => null,
                'attribute_filters' => [],
                'must' => $this->fallbackKeywords($query),
                'should' => [],
            ];
        }

        $rewritten = isset($data['rewritten_query']) && is_string($data['rewritten_query'])
            ? trim($data['rewritten_query'])
            : $query;

        $must = [];
        if (isset($data['must']) && is_array($data['must'])) {
            foreach ($data['must'] as $kw) {
                if (!is_string($kw)) {
                    continue;
                }
                $kw = trim($kw);
                if ($kw === '') {
                    continue;
                }
                $must[] = $kw;
            }
        }

        $should = [];
        if (isset($data['should']) && is_array($data['should'])) {
            foreach ($data['should'] as $kw) {
                if (!is_string($kw)) {
                    continue;
                }
                $kw = trim($kw);
                if ($kw === '') {
                    continue;
                }
                $should[] = $kw;
            }
        }

        $must = array_values(array_unique($must));
        $should = array_values(array_unique($should));

        $must = $this->normalizeTerms($must);
        $should = $this->normalizeTerms($should);

        if ($must === [] && $should === []) {
            $must = $this->fallbackKeywords($rewritten);
        }

        // legacy keywords: small merged list
        $keywords = array_slice(array_values(array_unique(array_merge($must, $should))), 0, 10);

        $category = null;
        if (isset($data['category']) && is_string($data['category'])) {
            $cand = trim($data['category']);
            if ($cand !== '' && in_array($cand, $categories, true)) {
                $category = $cand;
            }
        }

        $brand = null;
        if (isset($data['brand']) && is_string($data['brand'])) {
            $cand = trim($data['brand']);
            if ($cand !== '') {
                $brand = $cand;
            }
        }

        $attributeFilters = [];
        if (isset($data['attribute_filters']) && is_array($data['attribute_filters'])) {
            foreach ($data['attribute_filters'] as $f) {
                if (!is_array($f)) {
                    continue;
                }
                $name = isset($f['name']) && is_string($f['name']) ? trim($f['name']) : '';
                if ($name === '') {
                    continue;
                }
                $value = null;
                if (array_key_exists('value', $f) && is_string($f['value'])) {
                    $v = trim($f['value']);
                    $value = $v === '' ? null : $v;
                }
                $attributeFilters[] = [
                    'name' => $name,
                    'value' => $value,
                ];
            }
        }

        return [
            'original_query' => $query,
            'rewritten_query' => $rewritten !== '' ? $rewritten : $query,
            'keywords' => array_slice($keywords, 0, 10),
            'category' => $category,
            'brand' => $brand,
            'attribute_filters' => $attributeFilters,
            'must' => array_slice($must, 0, 6),
            'should' => array_slice($should, 0, 6),
        ];
    }

    private function normalizeTerms(array $terms): array
    {
        $out = [];

        foreach ($terms as $t) {
            if (!is_string($t)) {
                continue;
            }
            $t = trim($t);
            if ($t === '') {
                continue;
            }

            // Ha mégis több szóból állt, bontsuk szét (AI néha mégis adhat ilyet)
            $parts = preg_split('/\s+/u', $t) ?: [];
            $parts = array_values(array_filter(array_map('trim', $parts), fn ($p) => $p !== ''));

            foreach ($parts as $p) {
                $out[] = $p;

                $accentless = $this->removeHungarianAccents($p);
                if ($accentless !== $p) {
                    $out[] = $accentless;
                }

                // tizedes elválasztó variáns: 3.2 <-> 3,2
                if (preg_match('/\d[\.,]\d/u', $p)) {
                    $out[] = str_replace('.', ',', $p);
                    $out[] = str_replace(',', '.', $p);
                }
            }
        }

        $out = array_values(array_unique(array_filter(array_map('trim', $out), fn ($v) => $v !== '')));

        return $out;
    }

    private function removeHungarianAccents(string $value): string
    {
        return strtr($value, [
            'á' => 'a', 'Á' => 'A',
            'é' => 'e', 'É' => 'E',
            'í' => 'i', 'Í' => 'I',
            'ó' => 'o', 'Ó' => 'O',
            'ö' => 'o', 'Ö' => 'O',
            'ő' => 'o', 'Ő' => 'O',
            'ú' => 'u', 'Ú' => 'U',
            'ü' => 'u', 'Ü' => 'U',
            'ű' => 'u', 'Ű' => 'U',
        ]);
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
