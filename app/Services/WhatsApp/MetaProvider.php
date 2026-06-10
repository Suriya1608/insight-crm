<?php

namespace App\Services\WhatsApp;

use App\Models\Setting;
use App\Services\WhatsApp\Contracts\WhatsAppProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaProvider implements WhatsAppProviderInterface
{
    public function name(): string
    {
        return 'meta';
    }

    public function isConfigured(): bool
    {
        return (bool) $this->accessToken() && (bool) $this->phoneNumberId();
    }

    /**
     * Send a text message via Meta Cloud API.
     * Uses free-form text if inside the 24-hour inbound window, otherwise sends the default template.
     */
    public function sendText(string $to, string $body, bool $inbound24h = false, string $recipientName = ''): array
    {
        $token         = $this->accessToken();
        $phoneNumberId = $this->phoneNumberId();

        if (! $token || ! $phoneNumberId) {
            return [
                'ok'                  => false,
                'provider_message_id' => null,
                'provider'            => $this->name(),
                'error'               => 'Meta WhatsApp is not configured. Set token and Phone Number ID in Admin → Settings → WhatsApp.',
            ];
        }

        $templateName     = (string) Setting::get('meta_whatsapp_template_name',     config('whatsapp.template_name',     'hello_world'));
        $templateLanguage = (string) Setting::get('meta_whatsapp_template_language', config('whatsapp.template_language', 'en_US'));

        $templatePayload = [
            'name'     => $templateName,
            'language' => ['code' => $templateLanguage],
        ];

        // If the template uses {{1}} for the recipient name, pass it as a body component parameter.
        if ($recipientName !== '') {
            $templatePayload['components'] = [
                [
                    'type'       => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => $recipientName],
                    ],
                ],
            ];
        }

        $payload = $inbound24h
            ? [
                'messaging_product' => 'whatsapp',
                'recipient_type'    => 'individual',
                'to'                => $to,
                'type'              => 'text',
                'text'              => ['preview_url' => false, 'body' => $body],
            ]
            : [
                'messaging_product' => 'whatsapp',
                'recipient_type'    => 'individual',
                'to'                => $to,
                'type'              => 'template',
                'template'          => $templatePayload,
            ];

        try {
            $http = Http::withToken($token)->timeout(15)->asJson();
            if (app()->environment('local')) {
                $http = $http->withoutVerifying();
            }

            $response = $http->post(
                "https://graph.facebook.com/{$this->graphApiVersion()}/{$phoneNumberId}/messages",
                $payload
            );

            if (! $response->successful()) {
                $errCode = $response->json('error.code');
                $error   = $response->json('error.message', 'Unknown Meta API error');
                Log::error('MetaProvider::sendText failed', ['to' => $to, 'error' => $error, 'code' => $errCode]);

                if ($errCode === 190 || str_contains(strtolower($error), 'auth') || str_contains(strtolower($error), 'token')) {
                    return ['ok' => false, 'provider_message_id' => null, 'provider' => $this->name(),
                            'error' => 'Meta token expired — update it in Admin → Settings → WhatsApp.'];
                }
                if (in_array($errCode, [132000, 132001, 132005, 132007, 132012, 132015, 132016])) {
                    return ['ok' => false, 'provider_message_id' => null, 'provider' => $this->name(),
                            'error' => "Template error ({$errCode}): check template name \"{$templateName}\" and language \"{$templateLanguage}\" in Admin → Settings → WhatsApp."];
                }
                if ($errCode === 100 || str_contains($error, 'missing permissions')) {
                    return ['ok' => false, 'provider_message_id' => null, 'provider' => $this->name(),
                            'error' => 'Phone Number ID is wrong or your token lacks permission — check Admin → Settings → WhatsApp.'];
                }

                return ['ok' => false, 'provider_message_id' => null, 'provider' => $this->name(),
                        'error' => 'Meta API: ' . $error];
            }

            return [
                'ok'                  => true,
                'provider_message_id' => $response->json('messages.0.id'),
                'provider'            => $this->name(),
                'error'               => null,
            ];

        } catch (\Throwable $e) {
            Log::error('MetaProvider::sendText exception', ['to' => $to, 'error' => $e->getMessage()]);
            return ['ok' => false, 'provider_message_id' => null, 'provider' => $this->name(),
                    'error' => $e->getMessage()];
        }
    }

    /**
     * Verify that a template exists in the Meta Business Account.
     * Returns ['exists' => bool|null, 'status' => string|null, 'error' => string|null]
     * exists=null means Meta is not configured — caller decides whether to block.
     */
    public function verifyTemplate(string $templateName, string $language = ''): array
    {
        $token  = $this->accessToken();
        $wabaId = (string) config('whatsapp.business_account_id', env('META_WHATSAPP_BUSINESS_ACCOUNT_ID', ''));

        if (! $token || ! $wabaId) {
            return ['exists' => null, 'status' => null, 'error' => 'Meta token or Business Account ID not configured.'];
        }

        try {
            $http = Http::withToken($token)->timeout(10)->asJson();
            if (app()->environment('local')) {
                $http = $http->withoutVerifying();
            }

            $response = $http->get(
                "https://graph.facebook.com/{$this->graphApiVersion()}/{$wabaId}/message_templates",
                ['name' => $templateName, 'fields' => 'name,status,language']
            );

            if (! $response->successful()) {
                $errCode = $response->json('error.code');
                $error   = $response->json('error.message', 'Unknown Meta API error');
                return ['exists' => null, 'status' => null, 'error' => "Meta API ({$errCode}): {$error}"];
            }

            $data = $response->json('data', []);

            if (empty($data)) {
                return ['exists' => false, 'status' => null, 'error' => null];
            }

            if ($language !== '') {
                $lang = strtolower(str_replace('-', '_', $language));
                foreach ($data as $tpl) {
                    $tplLang = strtolower(str_replace('-', '_', $tpl['language'] ?? ''));
                    if ($tplLang === $lang || substr($tplLang, 0, 2) === substr($lang, 0, 2)) {
                        return ['exists' => true, 'status' => $tpl['status'] ?? null, 'error' => null];
                    }
                }
            }

            $first = $data[0];
            return ['exists' => true, 'status' => $first['status'] ?? null, 'error' => null];

        } catch (\Throwable $e) {
            Log::error('MetaProvider::verifyTemplate exception', ['template' => $templateName, 'error' => $e->getMessage()]);
            return ['exists' => null, 'status' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send a specific approved template (used for bulk blasts).
     */
    public function sendTemplate(string $to, string $templateName, string $recipientName = '', string $language = 'en_US'): array
    {
        $token         = $this->accessToken();
        $phoneNumberId = $this->phoneNumberId();

        if (! $token || ! $phoneNumberId) {
            return ['ok' => false, 'provider_message_id' => null, 'provider' => $this->name(),
                    'error' => 'Meta WhatsApp is not configured.'];
        }

        $templatePayload = [
            'name'     => $templateName,
            'language' => ['code' => $language],
        ];

        if ($recipientName !== '') {
            $templatePayload['components'] = [
                ['type' => 'body', 'parameters' => [['type' => 'text', 'text' => $recipientName]]],
            ];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $to,
            'type'              => 'template',
            'template'          => $templatePayload,
        ];

        try {
            $http = Http::withToken($token)->timeout(15)->asJson();
            if (app()->environment('local')) {
                $http = $http->withoutVerifying();
            }

            $response = $http->post(
                "https://graph.facebook.com/{$this->graphApiVersion()}/{$phoneNumberId}/messages",
                $payload
            );

            if (! $response->successful()) {
                $errCode = $response->json('error.code');
                $error   = $response->json('error.message', 'Unknown Meta API error');
                Log::error('MetaProvider::sendTemplate failed', ['to' => $to, 'template' => $templateName, 'error' => $error]);

                if (in_array($errCode, [132000, 132001, 132005, 132007, 132012, 132015, 132016])) {
                    return ['ok' => false, 'provider_message_id' => null, 'provider' => $this->name(),
                            'error' => "Template \"{$templateName}\" not found or not approved in Meta (error {$errCode}). Create and approve it in Meta Business Manager first."];
                }

                return ['ok' => false, 'provider_message_id' => null, 'provider' => $this->name(),
                        'error' => "Meta API ({$errCode}): {$error}"];
            }

            return [
                'ok'                  => true,
                'provider_message_id' => $response->json('messages.0.id'),
                'provider'            => $this->name(),
                'error'               => null,
            ];
        } catch (\Throwable $e) {
            Log::error('MetaProvider::sendTemplate exception', ['to' => $to, 'error' => $e->getMessage()]);
            return ['ok' => false, 'provider_message_id' => null, 'provider' => $this->name(),
                    'error' => $e->getMessage()];
        }
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function accessToken(): string
    {
        return (string) config('whatsapp.token', env('META_WHATSAPP_TOKEN', ''));
    }

    public function phoneNumberId(): string
    {
        return (string) config('whatsapp.phone_number_id', env('META_WHATSAPP_PHONE_NUMBER_ID', ''));
    }

    private function graphApiVersion(): string
    {
        return config('services.meta.graph_api_version', 'v22.0');
    }
}
