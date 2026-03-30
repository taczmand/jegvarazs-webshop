<?php

namespace App\Console\Commands;

use App\Models\BasicData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RefreshFacebookToken extends Command
{
    protected $signature = 'facebook:refresh-token';
    protected $description = 'Refresh Facebook long-lived user token and page token';

    public function handle()
    {
        try {
            $facebook_options = BasicData::where('key', 'LIKE', 'facebook%')
                ->pluck('value', 'key');

            $requiredKeys = [
                'facebook_app_id',
                'facebook_app_secret',
                'facebook_page_token',
                'facebook_page_id',
            ];

            foreach ($requiredKeys as $key) {
                if (!isset($facebook_options[$key])) {
                    throw new \Exception("Missing config key: {$key}");
                }
            }

            $app_id        = $facebook_options['facebook_app_id'];
            $app_secret    = $facebook_options['facebook_app_secret'];
            $current_token = $facebook_options['facebook_page_token'];
            $page_id       = $facebook_options['facebook_page_id'];

            // 🔁 1. USER TOKEN FRISSÍTÉS
            $response = Http::timeout(10)
                ->retry(3, 1000)
                ->get('https://graph.facebook.com/v19.0/oauth/access_token', [
                    'grant_type'        => 'fb_exchange_token',
                    'client_id'         => $app_id,
                    'client_secret'     => $app_secret,
                    'fb_exchange_token' => $current_token,
                ]);

            if (!$response->ok()) {
                Log::error('FB user token refresh failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                $this->error('User token refresh failed');
                return 1;
            }

            $newUserToken = $response->json()['access_token'] ?? null;
            $expiresIn    = $response->json()['expires_in'] ?? null;

            if (!$newUserToken) {
                throw new \Exception('No access_token in response');
            }

            // 🔁 2. PAGE TOKEN LEKÉRÉS
            $pageResponse = Http::timeout(10)
                ->retry(3, 1000)
                ->get('https://graph.facebook.com/v19.0/me/accounts', [
                    'access_token' => $newUserToken,
                ]);

            if (!$pageResponse->ok()) {
                Log::error('FB page token fetch failed', [
                    'status' => $pageResponse->status(),
                    'body'   => $pageResponse->body(),
                ]);

                $this->error('Page token fetch failed');
                return 1;
            }

            $pages = $pageResponse->json()['data'] ?? [];

            $page = collect($pages)->firstWhere('id', $page_id);

            if (!$page || empty($page['access_token'])) {
                Log::error('FB page not found or missing token', [
                    'page_id' => $page_id,
                ]);

                $this->error('Page token not found');
                return 1;
            }

            $pageToken = $page['access_token'];

            // 💾 3. MENTÉS
            BasicData::where('key', 'facebook_page_token')
                ->update(['value' => $pageToken]);

            if ($expiresIn) {
                BasicData::updateOrCreate(
                    ['key' => 'facebook_token_expires_at'],
                    ['value' => now()->addSeconds($expiresIn)]
                );
            }

            // ✅ LOG (token nélkül!)
            Log::info('Facebook token refreshed successfully', [
                'page_id' => $page_id,
                'expires_in' => $expiresIn,
            ]);

            $this->info('Facebook tokens refreshed successfully.');

            return 0;

        } catch (\Throwable $e) {
            Log::error('Facebook token refresh exception', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            $this->error($e->getMessage());
            return 1;
        }
    }
}
