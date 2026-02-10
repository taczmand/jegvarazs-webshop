<?php

namespace App\Http\Controllers;

use App\Models\BasicData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Lead;
use Carbon\Carbon;

class FacebookWebhookController extends Controller
{
    private $facebook_options;
    public function __construct()
    {
        $this->facebook_options = BasicData::where('key', 'LIKE', 'facebook%')
            ->pluck('value', 'key');

    }
    /**
     * FB webhook verify (GET)
     */
    public function verify(Request $request)
    {
        $verifyToken = $this->facebook_options['facebook_verify_token'];

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

            if (!empty($leadData['field_data'])) {
                $leadData['field_data'] = $this->normalizeLeadFieldData($formId, $leadId, $leadData['field_data']);
            }

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
                    'status'         => 'Új',
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
        $url = "https://graph.facebook.com/v19.0/$leadId";
        $response = Http::get($url, [
            'fields' => 'created_time,field_data,ad_id,adset_id,campaign_id',
            'access_token' => $this->facebook_options['facebook_page_token'],
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
                'access_token' => $this->facebook_options['facebook_page_token'],
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
        $response = Http::get("https://graph.facebook.com/v19.0/$formId", [
            'fields' => 'name',
            'access_token' => $this->facebook_options['facebook_page_token'],
        ]);

        if ($response->successful()) {
            return $response->json()['name'] ?? null;
        } else {
            Log::error('Failed to fetch form name', [
                'form_id' => $formId,
                'response' => $response->body()
            ]);
        }

        return null;
    }

    private function normalizeLeadFieldData($formId, $leadId, array $fieldData): array
    {
        $meta = $this->getFormMeta($formId);
        $optionMap = $meta['option_map'] ?? [];
        $allowedNames = $meta['allowed_names'] ?? [];
        $nameMap = $meta['name_map'] ?? [];
        $standardNames = $this->getStandardLeadFieldNames();

        if (!empty($allowedNames)) {
            $unknown = [];
            $filtered = [];

            foreach ($fieldData as $field) {
                $rawName = $field['name'] ?? null;
                $name = is_string($rawName) ? mb_strtolower(trim($rawName)) : null;
                if (!$name) {
                    continue;
                }

                if (!isset($allowedNames[$name])) {
                    $unknown[] = $rawName;
                    continue;
                }

                $filtered[] = $field;
            }

            if (!empty($unknown)) {
                Log::warning('FB lead field_data contains unknown field names for form', [
                    'lead_id' => $leadId,
                    'form_id' => $formId,
                    'unknown_names' => array_values(array_unique($unknown)),
                ]);
            }

            $fieldData = $filtered;
        }

        foreach ($fieldData as $i => $field) {
            $rawName = $field['name'] ?? null;
            if (!is_string($rawName)) {
                continue;
            }

            $normalized = mb_strtolower(trim($rawName));
            if ($normalized === '' || isset($standardNames[$normalized])) {
                continue;
            }

            if (isset($nameMap[$normalized])) {
                $fieldData[$i]['name'] = $nameMap[$normalized];
            }
        }

        if (empty($optionMap)) {
            return $fieldData;
        }

        foreach ($fieldData as $i => $field) {
            if (!isset($field['values']) || !is_array($field['values'])) {
                continue;
            }

            $fieldData[$i]['values'] = array_map(function ($value) use ($optionMap) {
                $value = is_scalar($value) ? (string) $value : $value;
                return (is_string($value) && isset($optionMap[$value])) ? $optionMap[$value] : $value;
            }, $field['values']);
        }

        return $fieldData;
    }

    private function getFormMeta($formId): array
    {
        try {
            $token = $this->facebook_options['facebook_page_token'];

            $responses = [];

            $responses['fields_questions'] = Http::get("https://graph.facebook.com/v19.0/$formId", [
                'fields' => 'questions',
                'access_token' => $token,
            ]);

            $responses['fields_questions_expanded'] = Http::get("https://graph.facebook.com/v19.0/$formId", [
                'fields' => 'questions{key,label,question,text,title,name,options}',
                'access_token' => $token,
            ]);

            $responses['edge_questions'] = Http::get("https://graph.facebook.com/v19.0/$formId/questions", [
                'fields' => 'key,label,question,text,title,name,options',
                'access_token' => $token,
            ]);

            $questions = null;

            foreach ($responses as $key => $response) {
                if (!$response->successful()) {
                    Log::warning('Failed to fetch form questions variant', [
                        'form_id' => $formId,
                        'variant' => $key,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    continue;
                }

                $json = $response->json();

                $candidate = $json['questions'] ?? null;
                if (is_array($candidate)) {
                    $questions = $candidate;
                    break;
                }

                $candidate = $json['data'] ?? null;
                if (is_array($candidate)) {
                    $questions = $candidate;
                    break;
                }
            }

            if (!is_array($questions)) {
                return [
                    'option_map' => [],
                    'allowed_names' => [],
                    'name_map' => [],
                ];
            }

            $optionMap = [];
            $allowedNames = $this->getStandardLeadFieldNames();
            $nameMap = [];
            foreach ($questions as $q) {
                $label = $this->getQuestionLabel($q);
                $labelNormalized = is_string($label) ? mb_strtolower(trim($label)) : null;

                foreach (['key', 'name', 'label', 'question', 'text', 'title'] as $nameKey) {
                    $candidate = $q[$nameKey] ?? null;
                    if (is_string($candidate)) {
                        $candidate = mb_strtolower(trim($candidate));
                        if ($candidate !== '') {
                            $allowedNames[$candidate] = true;

                            if ($labelNormalized && $candidate !== $labelNormalized) {
                                $nameMap[$candidate] = $label;
                            }
                        }
                    }
                }

                $options = $q['options'] ?? null;
                if (!is_array($options)) {
                    continue;
                }

                foreach ($options as $opt) {
                    $key = $opt['key'] ?? null;
                    $label = $opt['value'] ?? null;
                    if (is_string($key) && $key !== '' && is_string($label) && $label !== '') {
                        $optionMap[$key] = $label;
                    }
                }
            }

            return [
                'option_map' => $optionMap,
                'allowed_names' => $allowedNames,
                'name_map' => $nameMap,
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to fetch form questions', [
                'form_id' => $formId,
                'error' => $e->getMessage(),
            ]);
            return [
                'option_map' => [],
                'allowed_names' => [],
                'name_map' => [],
            ];
        }
    }

    private function getQuestionLabel(array $question): ?string
    {
        foreach (['label', 'question', 'text', 'title', 'name', 'key'] as $k) {
            $v = $question[$k] ?? null;
            if (is_string($v)) {
                $v = trim($v);
                if ($v !== '') {
                    return $v;
                }
            }
        }

        return null;
    }

    private function getStandardLeadFieldNames(): array
    {
        $names = [
            // FB standard lead fields
            'full_name',
            'first_name',
            'last_name',
            'email',
            'email_address',
            'phone_number',
            'phone',
            'city',
            'location',
        ];

        $set = [];
        foreach ($names as $n) {
            $set[mb_strtolower($n)] = true;
        }

        return $set;
    }

    /**
     * Mezők mappelése field_data-ból
     */
    private function mapLeadFields($fieldData)
    {
        $map = [
            'full_name' => ['full_name', 'teljes_név', 'teljes név', 'név', 'name', 'Teljes név', 'first_name', 'last_name', 'utónév', 'First name', 'Last name', 'Full name', 'Full Name', 'Name', 'First Name', 'Last Name'],
            'email'     => ['email', 'e-mail', 'email_address', 'e-mail_cím'],
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
