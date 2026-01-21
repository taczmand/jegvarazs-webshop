<?php

namespace App\Console\Commands;

use App\Models\BasicData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RefreshFacebookToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facebook:refresh-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $facebook_options = BasicData::where('key', 'LIKE', 'facebook%')
            ->pluck('value', 'key');

        $app_id = $facebook_options['facebook_app_id'];
        $app_secret = $facebook_options['facebook_app_secret'];
        $current_token = $facebook_options['facebook_page_token'];
        $page_id = $facebook_options['facebook_page_id'];

        // 1. Long-lived user token frissítés
        $response = Http::get('https://graph.facebook.com/v19.0/oauth/access_token', [
            'grant_type'        => 'fb_exchange_token',
            'client_id'         => $app_id,
            'client_secret'     => $app_secret,
            'fb_exchange_token' => $current_token,
        ]);

        Log::info('FB token refresh response', $response->json());

        if (!$response->ok()) {
            $this->error('User token refresh failed');
            Log::error('FB token refresh failed', $response->json());
            return 1;
        }

        $newPageToken = $response->json()['access_token'];

        BasicData::where('key', 'facebook_page_token')->update(['value' => $newPageToken]);

        $this->info('Facebook tokens refreshed successfully.');
        return 0;
    }
}
