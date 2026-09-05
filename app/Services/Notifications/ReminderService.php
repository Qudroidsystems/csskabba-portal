<?php

namespace App\Services\Notifications;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class ReminderService
{
    /**
     * Send fee reminders for selected students on chosen channels.
     *
     * @param  array<int>  $studentIds
     * @param  array<string>  $channels  email|sms|whatsapp
     * @return array{message:string,summary:array}
     */
    public function sendFeeReminders(
        array $studentIds,
        array $channels,
        ?int $termId = null,
        ?int $sessionId = null
    ): array {
        $channels = $this->normalizeChannels($channels);

        if (empty($channels)) {
            return [
                'message' => 'No channels selected.',
                'summary' => [],
            ];
        }

        $students = $this->loadStudentsWithDebt($studentIds, $termId, $sessionId);

        $summary = [];
        foreach ($channels as $channel) {
            $summary[$channel] = ['sent' => 0, 'skipped' => 0, 'failed' => 0];
        }

        $sentBy = Auth::id();

        foreach ($students as $student) {
            $contacts = $this->resolveContacts($student);
            $message  = $this->buildMessage($student);

            foreach ($channels as $channel) {
                $result = $this->sendOnChannel($channel, $student, $contacts, $message);

                $summary[$channel][$result['status'] === 'sent' ? 'sent' : ($result['status'] === 'failed' ? 'failed' : 'skipped')]++;

                DB::table('reminder_logs')->insert([
                    'student_id'        => $student->student_id,
                    'term_id'           => $termId,
                    'session_id'        => $sessionId,
                    'channel'           => $channel,
                    'recipient'         => $result['recipient'],
                    'status'            => $result['status'],
                    'reason'            => $result['reason'],
                    'outstanding'       => $student->outstanding,
                    'sent_by'           => $sentBy,
                    'provider_response' => $result['provider_response'] ?? null,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
        }

        $message = $this->formatSummaryMessage(count($students), $summary);

        return [
            'message' => $message,
            'summary' => $summary,
        ];
    }

    protected function normalizeChannels(array $channels): array
    {
        $allowed = ['email', 'sms', 'whatsapp'];
        $enabled = config('reminders.channels', []);

        return collect($channels)
            ->map(fn ($c) => strtolower(trim((string) $c)))
            ->filter(fn ($c) => in_array($c, $allowed, true))
            ->filter(fn ($c) => ($enabled[$c] ?? false) === true)
            ->unique()
            ->values()
            ->all();
    }

    protected function loadStudentsWithDebt(array $studentIds, ?int $termId, ?int $sessionId): Collection
    {
        $query = DB::table('student_bill_payment_book as sbpb')
            ->join('studentRegistration as s', 's.id', '=', 'sbpb.student_id')
            ->leftJoin('schoolclass as sc', 'sc.id', '=', 'sbpb.class_id')
            ->leftJoin('schoolarm as sa', 'sa.id', '=', 'sc.arm')
            ->leftJoin('schoolterm as st', 'st.id', '=', 'sbpb.term_id')
            ->leftJoin('schoolsession as ss', 'ss.id', '=', 'sbpb.session_id')
            ->whereIn('sbpb.student_id', $studentIds)
            ->where('sbpb.amount_owed', '>', 0)
            ->select(
                's.id as student_id',
                's.firstname',
                's.lastname',
                's.admissionNo',
                DB::raw("TRIM(CONCAT(COALESCE(sc.schoolclass,''), ' ', COALESCE(sa.arm,''))) as class_name"),
                'st.term as term_name',
                'ss.session as session_name',
                DB::raw('SUM(sbpb.amount_owed) as outstanding'),
                DB::raw('SUM(sbpb.amount_paid) as amount_paid')
            )
            ->groupBy(
                's.id',
                's.firstname',
                's.lastname',
                's.admissionNo',
                'sc.schoolclass',
                'sa.arm',
                'st.term',
                'ss.session'
            );

        // Select contact columns that exist (safe)
        $table = config('reminders.contacts.student_table', 'studentRegistration');
        $emailFields = config('reminders.contacts.email_fields', []);
        $phoneFields = config('reminders.contacts.phone_fields', []);

        foreach (array_merge($emailFields, $phoneFields) as $col) {
            if ($this->columnExists($table, $col)) {
                $query->addSelect('s.' . $col);
            }
        }

        if ($termId) {
            $query->where('sbpb.term_id', $termId);
        }
        if ($sessionId) {
            $query->where('sbpb.session_id', $sessionId);
        }

        return $query->get();
    }

    protected function columnExists(string $table, string $column): bool
    {
        static $cache = [];
        $key = $table . '.' . $column;
        if (!array_key_exists($key, $cache)) {
            try {
                $cache[$key] = DB::getSchemaBuilder()->hasColumn($table, $column);
            } catch (\Throwable $e) {
                $cache[$key] = false;
            }
        }
        return $cache[$key];
    }

    protected function resolveContacts(object $student): array
    {
        $email = null;
        foreach (config('reminders.contacts.email_fields', []) as $field) {
            if (!empty($student->{$field})) {
                $email = trim((string) $student->{$field});
                break;
            }
        }

        $phone = null;
        foreach (config('reminders.contacts.phone_fields', []) as $field) {
            if (!empty($student->{$field})) {
                $phone = $this->normalizePhone((string) $student->{$field});
                break;
            }
        }

        return [
            'email' => $email,
            'phone' => $phone,
        ];
    }

    /**
     * Nigeria-friendly: 080... → 23480...
     */
    protected function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === null || $digits === '') {
            return null;
        }

        if (str_starts_with($digits, '234') && strlen($digits) >= 13) {
            return $digits;
        }
        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            return '234' . substr($digits, 1);
        }
        if (strlen($digits) === 10) {
            return '234' . $digits;
        }

        return $digits;
    }

    protected function buildMessage(object $student): array
    {
        $name = trim(($student->firstname ?? '') . ' ' . ($student->lastname ?? ''));
        $school = config('reminders.school_name', config('app.name'));
        $outstanding = number_format((float) $student->outstanding, 2);
        $term = $student->term_name ?: 'current term';
        $session = $student->session_name ?: 'current session';
        $class = $student->class_name ?: '';

        $subject = "Fee payment reminder – {$name}";

        $body = "Dear Parent/Guardian,\n\n"
            . "This is a reminder that {$name} ({$student->admissionNo})"
            . ($class ? " in {$class}" : '')
            . " has an outstanding school fee balance of ₦{$outstanding}"
            . " for {$term}, {$session}.\n\n"
            . "Please arrange payment at your earliest convenience.\n\n"
            . "Thank you.\n{$school}";

        $sms = "Fee reminder: {$name} owes ₦{$outstanding} for {$term}. Please pay promptly. – {$school}";

        return [
            'subject' => $subject,
            'body'    => $body,
            'sms'     => mb_substr($sms, 0, 320),
            'name'    => $name,
            'outstanding' => $outstanding,
            'term'    => $term,
            'session' => $session,
        ];
    }

    protected function sendOnChannel(string $channel, object $student, array $contacts, array $message): array
    {
        return match ($channel) {
            'email'    => $this->sendEmail($contacts['email'], $message),
            'sms'      => $this->sendSms($contacts['phone'], $message),
            'whatsapp' => $this->sendWhatsapp($contacts['phone'], $message),
            default    => [
                'status' => 'skipped',
                'recipient' => null,
                'reason' => 'Unknown channel',
            ],
        };
    }

    protected function sendEmail(?string $email, array $message): array
    {
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'status' => 'skipped',
                'recipient' => $email,
                'reason' => 'No valid email',
            ];
        }

        try {
            Mail::raw($message['body'], function ($mail) use ($email, $message) {
                $mail->to($email)->subject($message['subject']);
            });

            return [
                'status' => 'sent',
                'recipient' => $email,
                'reason' => null,
            ];
        } catch (\Throwable $e) {
            Log::error('Reminder email failed: ' . $e->getMessage());
            return [
                'status' => 'failed',
                'recipient' => $email,
                'reason' => $e->getMessage(),
                'provider_response' => $e->getMessage(),
            ];
        }
    }

    protected function sendSms(?string $phone, array $message): array
    {
        if (!$phone) {
            return [
                'status' => 'skipped',
                'recipient' => null,
                'reason' => 'No phone number',
            ];
        }

        $driver = config('reminders.sms.driver', 'log');

        try {
            if ($driver === 'termii') {
                return $this->sendSmsTermii($phone, $message['sms']);
            }
            if ($driver === 'africastalking') {
                return $this->sendSmsAfricaTalking($phone, $message['sms']);
            }

            // log driver – always "sent" for testing
            Log::info('SMS reminder (log driver)', ['to' => $phone, 'text' => $message['sms']]);
            return [
                'status' => 'sent',
                'recipient' => $phone,
                'reason' => 'Logged only (SMS_DRIVER=log)',
                'provider_response' => 'log',
            ];
        } catch (\Throwable $e) {
            Log::error('Reminder SMS failed: ' . $e->getMessage());
            return [
                'status' => 'failed',
                'recipient' => $phone,
                'reason' => $e->getMessage(),
                'provider_response' => $e->getMessage(),
            ];
        }
    }

    protected function sendSmsTermii(string $phone, string $text): array
    {
        $apiKey = config('reminders.sms.termii.api_key');
        $sender = config('reminders.sms.termii.sender');
        $url    = config('reminders.sms.termii.url');

        if (!$apiKey) {
            return [
                'status' => 'skipped',
                'recipient' => $phone,
                'reason' => 'Termii not configured',
            ];
        }

        $response = Http::post($url, [
            'to' => $phone,
            'from' => $sender,
            'sms' => $text,
            'type' => 'plain',
            'channel' => 'generic',
            'api_key' => $apiKey,
        ]);

        if ($response->successful()) {
            return [
                'status' => 'sent',
                'recipient' => $phone,
                'reason' => null,
                'provider_response' => $response->body(),
            ];
        }

        return [
            'status' => 'failed',
            'recipient' => $phone,
            'reason' => 'Termii error',
            'provider_response' => $response->body(),
        ];
    }

    protected function sendSmsAfricaTalking(string $phone, string $text): array
    {
        // Placeholder – implement with AT SDK or HTTP when ready
        Log::info('Africa\'s Talking SMS stub', ['to' => $phone, 'text' => $text]);
        return [
            'status' => 'sent',
            'recipient' => $phone,
            'reason' => 'Stub – implement AT API',
            'provider_response' => 'stub',
        ];
    }

    protected function sendWhatsapp(?string $phone, array $message): array
    {
        if (!$phone) {
            return [
                'status' => 'skipped',
                'recipient' => null,
                'reason' => 'No phone number',
            ];
        }

        $driver = config('reminders.whatsapp.driver', 'log');

        try {
            if ($driver === 'meta') {
                return $this->sendWhatsappMeta($phone, $message);
            }
            if ($driver === 'twilio') {
                return $this->sendWhatsappTwilio($phone, $message);
            }

            Log::info('WhatsApp reminder (log driver)', ['to' => $phone, 'text' => $message['sms']]);
            return [
                'status' => 'sent',
                'recipient' => $phone,
                'reason' => 'Logged only (WHATSAPP_DRIVER=log)',
                'provider_response' => 'log',
            ];
        } catch (\Throwable $e) {
            Log::error('Reminder WhatsApp failed: ' . $e->getMessage());
            return [
                'status' => 'failed',
                'recipient' => $phone,
                'reason' => $e->getMessage(),
                'provider_response' => $e->getMessage(),
            ];
        }
    }

    protected function sendWhatsappMeta(string $phone, array $message): array
    {
        $token  = config('reminders.whatsapp.meta.token');
        $phoneId = config('reminders.whatsapp.meta.phone_number_id');
        $template = config('reminders.whatsapp.meta.template_name');
        $lang = config('reminders.whatsapp.meta.template_language', 'en');

        if (!$token || !$phoneId) {
            return [
                'status' => 'skipped',
                'recipient' => $phone,
                'reason' => 'WhatsApp Meta not configured',
            ];
        }

        // Template message (required for outbound utility notices)
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'template',
            'template' => [
                'name' => $template,
                'language' => ['code' => $lang],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => $message['name']],
                            ['type' => 'text', 'text' => $message['outstanding']],
                            ['type' => 'text', 'text' => $message['term']],
                        ],
                    ],
                ],
            ],
        ];

        $response = Http::withToken($token)
            ->post("https://graph.facebook.com/v19.0/{$phoneId}/messages", $payload);

        if ($response->successful()) {
            return [
                'status' => 'sent',
                'recipient' => $phone,
                'reason' => null,
                'provider_response' => $response->body(),
            ];
        }

        return [
            'status' => 'failed',
            'recipient' => $phone,
            'reason' => 'Meta API error',
            'provider_response' => $response->body(),
        ];
    }

    protected function sendWhatsappTwilio(string $phone, array $message): array
    {
        $sid   = config('reminders.whatsapp.twilio.sid');
        $token = config('reminders.whatsapp.twilio.token');
        $from  = config('reminders.whatsapp.twilio.from');

        if (!$sid || !$token || !$from) {
            return [
                'status' => 'skipped',
                'recipient' => $phone,
                'reason' => 'Twilio WhatsApp not configured',
            ];
        }

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'From' => $from,
                'To'   => 'whatsapp:+' . ltrim($phone, '+'),
                'Body' => $message['body'],
            ]);

        if ($response->successful()) {
            return [
                'status' => 'sent',
                'recipient' => $phone,
                'reason' => null,
                'provider_response' => $response->body(),
            ];
        }

        return [
            'status' => 'failed',
            'recipient' => $phone,
            'reason' => 'Twilio error',
            'provider_response' => $response->body(),
        ];
    }

    protected function formatSummaryMessage(int $studentCount, array $summary): string
    {
        $parts = ["Reminders processed for {$studentCount} student(s)."];
        foreach ($summary as $channel => $stats) {
            $parts[] = strtoupper($channel) . ": {$stats['sent']} sent, {$stats['skipped']} skipped, {$stats['failed']} failed";
        }
        return implode("\n", $parts);
    }
}