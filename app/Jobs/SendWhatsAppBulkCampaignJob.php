<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\CampaignActivity;
use App\Models\WhatsAppMessage;
use App\Services\WhatsApp\MetaProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppBulkCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries   = 1;

    public function __construct(
        private int    $campaignId,
        private string $templateName,
        private string $templateLanguage = 'en_US',
        private int    $sentBy = 0,
    ) {}

    public function handle(): void
    {
        $campaign = Campaign::find($this->campaignId);
        if (! $campaign) return;

        $campaign->update(['wa_blast_status' => 'sending']);

        $provider = new MetaProvider();

        $check = $provider->verifyTemplate($this->templateName, $this->templateLanguage);
        if ($check['exists'] === false) {
            $errMsg = "Template \"{$this->templateName}\" not found in your Meta Business Account. Create and approve it in Meta Business Manager first.";
            $campaign->contacts()->where('wa_status', 'pending')->update(['wa_status' => 'failed', 'wa_error' => $errMsg]);
            $campaign->update([
                'wa_blast_status' => 'completed',
                'wa_failed_count' => $campaign->contacts()->where('wa_status', 'failed')->count(),
            ]);
            Log::error('WhatsApp blast aborted: template not found in Meta', [
                'campaign_id'   => $this->campaignId,
                'template_name' => $this->templateName,
            ]);
            return;
        }

        $sent     = 0;
        $failed   = 0;

        $campaign->contacts()
            ->where('wa_status', 'pending')
            ->chunkById(50, function ($contacts) use ($provider, $campaign, &$sent, &$failed) {
                foreach ($contacts as $contact) {
                    $phone = $this->normalizePhone($contact->phone ?? '');

                    if (! $phone) {
                        $contact->update(['wa_status' => 'failed', 'wa_error' => 'Invalid phone number']);
                        $failed++;
                        $campaign->increment('wa_failed_count');
                        continue;
                    }

                    $result = $provider->sendTemplate(
                        to:           $phone,
                        templateName: $this->templateName,
                        recipientName: $contact->name ?? '',
                        language:     $this->templateLanguage,
                    );

                    if ($result['ok']) {
                        $contact->update(['wa_status' => 'sent', 'wa_sent_at' => now(), 'wa_error' => null]);

                        WhatsAppMessage::create([
                            'campaign_contact_id' => $contact->id,
                            'lead_id'             => null,
                            'from_number'         => $phone,
                            'message_body'        => '[Template: ' . $this->templateName . ']',
                            'direction'           => 'outbound',
                            'provider_message_id' => $result['provider_message_id'],
                            'provider'            => 'meta',
                            'is_read'             => true,
                            'meta_data'           => ['template' => $this->templateName, 'blast' => true],
                        ]);

                        CampaignActivity::create([
                            'campaign_contact_id' => $contact->id,
                            'type'                => 'whatsapp',
                            'description'         => 'WhatsApp blast sent via template: ' . $this->templateName,
                            'meta'                => ['template' => $this->templateName, 'blast' => true],
                            'created_by'          => $this->sentBy ?: null,
                        ]);

                        $sent++;
                        $campaign->increment('wa_sent_count');
                    } else {
                        $contact->update(['wa_status' => 'failed', 'wa_error' => $result['error']]);
                        $failed++;
                        $campaign->increment('wa_failed_count');
                        Log::warning('WhatsApp blast send failed', [
                            'campaign_id' => $this->campaignId,
                            'contact_id'  => $contact->id,
                            'phone'       => $phone,
                            'error'       => $result['error'],
                        ]);
                    }

                    // ~2 messages/second — stay within Meta rate limits
                    usleep(500_000);
                }
            });

        $campaign->update(['wa_blast_status' => 'completed']);
    }

    public function failed(\Throwable $e): void
    {
        Campaign::where('id', $this->campaignId)->update(['wa_blast_status' => 'failed']);
        Log::error('SendWhatsAppBulkCampaignJob failed', [
            'campaign_id' => $this->campaignId,
            'error'       => $e->getMessage(),
        ]);
    }

    private function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (strlen($digits) === 10) {
            return '91' . $digits;
        }
        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return '91' . substr($digits, 1);
        }
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return $digits;
        }
        if (strlen($digits) >= 10) {
            return $digits;
        }

        return null;
    }
}
