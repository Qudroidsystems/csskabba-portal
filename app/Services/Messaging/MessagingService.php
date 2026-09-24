<?php

namespace App\Services\Messaging;

use App\Mail\NoticeMail;
use App\Models\MessagingSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends one message on one channel using the settings the admin saved in
 * Notices › Settings. Every method returns
 *   ['status' => sent|failed|skipped, 'error' => ?string, 'id' => ?string, 'retryable' => bool]
 */
class MessagingService
{
    protected array $settings = [];

    public function setting(string $channel): MessagingSetting
    {
        return $this->settings[$channel] ??= MessagingSetting::for($channel);
    }

    /** Channels switched on (log driver counts: it records instead of sending). */
    public function enabled(string $channel): bool
    {
        $s = $this->setting($channel);
        return $s->is_active && $s->isConfigured();
    }

    public function send(string $channel, string $to, string $body, array $context = []): array
    {
        if (!$this->enabled($channel)) {
            return $this->result('skipped', ucfirst($channel) . ' is switched off in notification settings.');
        }

        try {
            return match ($channel) {
                'sms'      => $this->sms($to, $body),
                'whatsapp' => $this->whatsapp($to, $body, $context),
                'email'    => $this->email($to, $body, $context),
                default    => $this->result('skipped', 'Unknown channel'),
            };
        } catch (\Throwable $e) {
            Log::error("Notice {$channel} send error", ['to' => $to, 'error' => $e->getMessage()]);
            return $this->result('failed', 'Could not reach the ' . $channel . ' provider: ' . mb_substr($e->getMessage(), 0, 200), null, true);
        }
    }

    // ── SMS (Termii) ────────────────────────────────────────────────────
    protected function sms(string $phone, string $text): array
    {
        $s = $this->setting('sms');

        if ($s->driver === 'log') {
            Log::info('[Notice SMS – log driver]', ['to' => $phone, 'text' => $text]);
            return $this->result('sent', null, 'log');
        }

        $res = Http::acceptJson()->timeout(30)->post(rtrim($s->value('base_url') ?: 'https://api.ng.termii.com', '/') . '/api/sms/send', [
            'api_key' => $s->value('api_key'),
            'to'      => $phone,
            'from'    => $s->value('sender_id'),
            'sms'     => $text,
            'type'    => 'plain',
            'channel' => $s->value('route') === 'dnd' ? 'dnd' : 'generic',
        ]);
        $body = $res->json() ?? [];

        if ($res->successful() && (strtolower((string) ($body['code'] ?? '')) === 'ok' || !empty($body['message_id']))) {
            return $this->result('sent', null, (string) ($body['message_id'] ?? ''));
        }

        $msg = $body['message'] ?? ('HTTP ' . $res->status());
        return $this->result('failed', 'Termii: ' . (is_string($msg) ? $msg : json_encode($msg)), null, $res->status() >= 500 || $res->status() === 429);
    }

    // ── WhatsApp (Meta Cloud API, approved template) ────────────────────
    protected function whatsapp(string $phone, string $text, array $context): array
    {
        $s = $this->setting('whatsapp');

        if ($s->driver === 'log') {
            Log::info('[Notice WhatsApp – log driver]', ['to' => $phone, 'text' => $text]);
            return $this->result('sent', null, 'log');
        }

        // Template body params may not contain new lines, tabs or 4+ spaces.
        $clean = fn ($v) => trim(preg_replace('/\s{2,}/', ' ', str_replace(["\r", "\n", "\t"], ' ', (string) $v)));

        $res = Http::withToken($s->value('access_token'))->acceptJson()->timeout(30)
            ->post('https://graph.facebook.com/' . $s->value('api_version') . '/' . $s->value('phone_number_id') . '/messages', [
                'messaging_product' => 'whatsapp',
                'to'       => $phone,
                'type'     => 'template',
                'template' => [
                    'name'     => $s->value('template_name'),
                    'language' => ['code' => $s->value('template_lang')],
                    'components' => [[
                        'type'       => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => mb_substr($clean($context['name'] ?? 'Parent'), 0, 60)],
                            ['type' => 'text', 'text' => mb_substr($clean($text), 0, 1000)],
                        ],
                    ]],
                ],
            ]);

        if ($res->successful()) {
            return $this->result('sent', null, (string) $res->json('messages.0.id'));
        }

        $err  = $res->json('error') ?? [];
        $code = (int) ($err['code'] ?? 0);
        $msg  = ($err['error_user_msg'] ?? null) ?: ($err['message'] ?? ('HTTP ' . $res->status()));
        $hint = match (true) {
            $code === 190                  => ' (access token expired or invalid)',
            in_array($code, [132000, 132001], true) => ' (template name/language not approved or parameter count wrong — the template needs exactly 2 variables)',
            $code === 131026               => ' (number is not on WhatsApp)',
            $code === 131047               => ' (re-engagement window closed — template required)',
            in_array($code, [4, 80007, 130429], true) => ' (rate limited, will retry)',
            default                        => '',
        };
        return $this->result('failed', 'WhatsApp: ' . $msg . $hint, null, in_array($code, [4, 80007, 130429], true) || $res->status() >= 500);
    }

    // ── Email ───────────────────────────────────────────────────────────
    protected function email(string $email, string $text, array $context): array
    {
        $s = $this->setting('email');
        Mail::to($email)->send(new NoticeMail(
            $context['subject'] ?? 'School notice',
            $text,
            $context['name'] ?? null,
            $s->value('from_name'),
            $s->value('reply_to'),
        ));
        return $this->result('sent');
    }

    // ── Helpers ─────────────────────────────────────────────────────────

    /** Nigeria-friendly: 0803… / 803… / +234803… → 234803…; null when not a phone. */
    public static function normalizePhone(?string $phone): ?string
    {
        $d = preg_replace('/\D+/', '', (string) $phone);
        if ($d === '' || $d === null) return null;
        if (str_starts_with($d, '234') && strlen($d) === 13) return $d;
        if (str_starts_with($d, '0') && strlen($d) === 11) return '234' . substr($d, 1);
        if (strlen($d) === 10 && in_array($d[0], ['7', '8', '9'], true)) return '234' . $d;
        return strlen($d) >= 11 && strlen($d) <= 15 ? $d : null; // other countries, already international
    }

    /** SMS pages: GSM-7 = 160 / 153 per page; any other character = 70 / 67. */
    public static function smsPages(string $text): array
    {
        $gsm = '@£$¥èéùìòÇ' . "\n" . 'Øø' . "\r" . 'ÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !"#¤%&\'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà';
        $ext = '^{}\\[~]|€';
        $unicode = false; $len = 0;
        foreach (mb_str_split($text) as $ch) {
            if (mb_strpos($gsm, $ch) !== false) { $len++; }
            elseif (mb_strpos($ext, $ch) !== false) { $len += 2; }
            else { $unicode = true; break; }
        }
        if ($unicode) {
            $len = mb_strlen($text);
            return ['chars' => $len, 'pages' => $len <= 70 ? 1 : (int) ceil($len / 67), 'unicode' => true];
        }
        return ['chars' => $len, 'pages' => $len <= 160 ? 1 : (int) ceil($len / 153), 'unicode' => false];
    }

    protected function result(string $status, ?string $error = null, ?string $id = null, bool $retryable = false): array
    {
        return ['status' => $status, 'error' => $error, 'id' => $id, 'retryable' => $retryable];
    }
}
