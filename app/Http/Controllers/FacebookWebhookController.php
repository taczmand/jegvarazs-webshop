<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Lead;

class FacebookWebhookController extends Controller
{
    /**
     * Webhook verify endpoint (Facebook GET ellenőrzése)
     */
    public function verify(Request $request)
    {
        $verifyToken = env('FB_VERIFY_TOKEN');

        if (
            $request->get('hub_mode') === 'subscribe' &&
            $request->get('hub_verify_token') === $verifyToken
        ) {
            \Log::info('FB Webhook verify', $request->all());
            return response($request->get('hub_challenge'), 200)->header('Content-Type', 'text/plain');
        }
        return response('Invalid verify token', 403);
    }

    /**
     * Facebook webhook POST – lead érkezett
     */
    public function handle(Request $request)
    {
        \Log::info('FB Lead Webhook received', [$request->all()]);

        // Biztonsági ellenőrzés
        if (!isset($request->entry[0]['changes'])) {
            return response('No changes', 200);
        }

        foreach ($request->entry[0]['changes'] as $change) {

            if (($change['field'] ?? null) !== 'leadgen') {
                continue;
            }

            $leadId = $change['value']['leadgen_id'];
            $formId = $change['value']['form_id'];

            // Lead részletek lekérése Graph API-ból
            $leadData = $this->getLeadDetails($leadId);
            \Log::info('leadData: ', [$leadData]);

            Lead::updateOrCreate(
                ['lead_id' => $leadId],
                [
                    'form_id' => $formId,
                    'data'    => json_encode($leadData)
                ]
            );
        }

        return response('OK', 200);
    }

    /**
     * Graf API hívás SDK nélkül
     * /{lead_id}?fields=field_data
     */
    private function getLeadDetails($leadId)
    {
        $pageToken = env('FB_PAGE_TOKEN');

        // Lead adatok lekérése
        $url = "https://graph.facebook.com/v19.0/$leadId";
        $response = Http::get($url, [
            'fields' => 'created_time,field_data,ad_id,adset_id,campaign_id',
            'access_token' => $pageToken,
        ]);

        if ($response->failed()) {
            \Log::error('Failed to fetch lead', [
                'lead_id' => $leadId,
                'response' => $response->body()
            ]);
            return [];
        }

        $leadData = $response->json();

        // Kampány nevét lekérjük, ha van campaign_id
        if (!empty($leadData['campaign_id'])) {
            $campaignResponse = Http::get("https://graph.facebook.com/v19.0/{$leadData['campaign_id']}", [
                'fields' => 'name',
                'access_token' => $pageToken,
            ]);

            if (!$campaignResponse->failed()) {
                $leadData['campaign_name'] = $campaignResponse->json()['name'] ?? null;
            } else {
                $leadData['campaign_name'] = null;
                \Log::warning('Failed to fetch campaign name', [
                    'campaign_id' => $leadData['campaign_id'],
                    'response' => $campaignResponse->body()
                ]);
            }
        }

        return $leadData;
    }

}
