<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Lead;
use Carbon\Carbon;

class FacebookWebhookController extends Controller
{
    /**
     * FB webhook verify (GET)
     */
    public function verify(Request $request)
    {
        $verifyToken = env('FB_VERIFY_TOKEN');

        if (
            $request->get('hub_mode') === 'subscribe' &&
            $request->get('hub_verify_token') === $verifyToken
        ) {
            Log::info('FB Webhook verify', $request->all());
            return response($request->get('hub_challenge'), 200)->header('Content-Type', 'text/plain');
        }
        return response('Invalid verify token', 403);
    }

    /**
     * Facebook webhook POST – lead érkezett
     */
    public function handle(Request $request)
    {
        Log::info('FB Lead Webhook received', [$request->all()]);

        if (!isset($request->entry[0]['changes'])) {
            return response('No changes', 200);
        }

        foreach ($request->entry[0]['changes'] as $change) {

            if (($change['field'] ?? null) !== 'leadgen') {
                continue;
            }

            $leadId = $change['value']['leadgen_id'];
            $formId = $change['value']['form_id'];

            // Lead + kampány + form name
            $leadData = $this->getLeadDetails($leadId);
            Log::info('leadData: ', [$leadData]);

            // Mezők kinyerése field_data-ból
            $mapped = $this->mapLeadFields($leadData['field_data'] ?? []);

            Log::info('mapped: ', [$mapped]);

            // Form name lekérése
            $formName = $this->getFormName($formId);

            Lead::updateOrCreate(
                ['lead_id' => $leadId],
                [
                    'form_id'        => $formId,
                    'form_name'      => $formName,
                    'full_name'      => $mapped['full_name'],
                    'email'          => $mapped['email'],
                    'phone'          => $mapped['phone'],
                    'city'           => $mapped['city'],
                    'campaign_name'  => $leadData['campaign_name'] ?? null,
                    'status'         => 'new',
                    'viewed_by'      => null,
                    'viewed_at'      => null,
                    'data'           => json_encode($leadData)
                ]
            );
        }

        return response('OK', 200);
    }

    /**
     * Lead részletek lekérése
     */
    private function getLeadDetails($leadId)
    {
        $pageToken = env('FB_PAGE_TOKEN');

        $url = "https://graph.facebook.com/v19.0/$leadId";
        $response = Http::get($url, [
            'fields' => 'created_time,field_data,ad_id,adset_id,campaign_id',
            'access_token' => $pageToken,
        ]);

        if ($response->failed()) {
            Log::error('Failed to fetch lead', [
                'lead_id' => $leadId,
                'response' => $response->body()
            ]);
            return [];
        }

        $leadData = $response->json();

        // Kampány név
        if (!empty($leadData['campaign_id'])) {
            $campaignResponse = Http::get("https://graph.facebook.com/v19.0/{$leadData['campaign_id']}", [
                'fields' => 'name',
                'access_token' => $pageToken,
            ]);

            $leadData['campaign_name'] = $campaignResponse->json()['name'] ?? null;
        }

        return $leadData;
    }

    /**
     * Form név lekérése Graph API-ból
     */
    private function getFormName($formId)
    {
        $pageToken = env('FB_PAGE_TOKEN');

        $response = Http::get("https://graph.facebook.com/v19.0/$formId", [
            'fields' => 'name',
            'access_token' => $pageToken,
        ]);

        if ($response->successful()) {
            return $response->json()['name'] ?? null;
        }

        return null;
    }

    /**
     * Mezők mappelése field_data-ból
     */
    private function mapLeadFields($fieldData)
    {
        $map = [
            'full_name' => ['full_name', 'teljes_név', 'teljes név', 'név', 'name', 'Teljes név', 'first_name', 'last_name', 'utónév', 'First name', 'Last name', 'Full name', 'Full Name', 'Name', 'First Name', 'Last Name'],
            'email'     => ['email', 'e-mail', 'email_address'],
            'phone'     => ['phone_number', 'telefonszám', 'telefon', 'phone'],
            'city'      => ['city', 'város', 'lakóhely', 'location', 'település'],
        ];

        $result = [
            'full_name' => null,
            'email'     => null,
            'phone'     => null,
            'city'      => null,
        ];

        foreach ($fieldData as $field) {
            $name = mb_strtolower($field['name']);
            $value = $field['values'][0] ?? null;

            foreach ($map as $key => $possibleNames) {
                foreach ($possibleNames as $poss) {
                    if ($name === mb_strtolower($poss)) {
                        $result[$key] = $value;
                    }
                }
            }
        }

        return $result;
    }
}
