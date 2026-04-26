<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class OpenAiClient
{
    public function chatJson(array $messages, array $options = []): ?array
    {
        $apiKey = (string) config('services.openai.key');
        if ($apiKey === '') {
            return null;
        }

        $model = (string) ($options['model'] ?? config('services.openai.model', 'gpt-4o-mini'));
        $temperature = (float) ($options['temperature'] ?? 0.2);

        $baseUrl = rtrim((string) config('services.openai.base_url', 'https://api.openai.com'), '/');
        $url = $baseUrl . '/v1/chat/completions';

        try {
            $res = Http::withToken($apiKey)
                ->timeout(15)
                ->post($url, [
                    'model' => $model,
                    'temperature' => $temperature,
                    'messages' => $messages,
                    'response_format' => [
                        'type' => 'json_object',
                    ],
                ]);
        } catch (ConnectionException) {
            return null;
        }

        if (!$res->ok()) {
            return null;
        }

        $content = $res->json('choices.0.message.content');
        if (!is_string($content) || trim($content) === '') {
            return null;
        }

        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            return null;
        }

        return $decoded;
    }
}
