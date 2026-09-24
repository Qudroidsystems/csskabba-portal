<?php

namespace App\Http\Controllers;

use App\Models\MessagingSetting;
use App\Services\Messaging\MessagingService;
use Illuminate\Http\Request;

/**
 * Notices › Settings: SMS (Termii), WhatsApp (Meta Cloud API) and email.
 * Keys are entered here (encrypted at rest), not in .env.
 */
class MessagingSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Manage notification settings');
    }

    public function index()
    {
        $settings = collect(['sms', 'whatsapp', 'email'])->mapWithKeys(fn ($c) => [$c => MessagingSetting::for($c)]);

        return view('notices.settings', [
            'pagetitle' => 'Notification Settings',
            'settings'  => $settings,
            'drivers'   => MessagingSetting::DRIVERS,
            'mailFrom'  => config('mail.from.address'),
            'mailer'    => config('mail.default'),
        ]);
    }

    public function update(Request $request, string $channel)
    {
        abort_unless(isset(MessagingSetting::DRIVERS[$channel]), 404);
        $s = MessagingSetting::for($channel);

        $data = $request->validate([
            'driver'    => 'required|in:' . implode(',', array_keys(MessagingSetting::DRIVERS[$channel])),
            'is_active' => 'nullable|boolean',
            'fields'    => 'nullable|array',
            'fields.*'  => 'nullable|string|max:2000',
            'clear'     => 'nullable|array',
        ]);

        $s->driver = $data['driver'];
        foreach ($s->fields() as $f => $def) {
            if (!empty($data['clear'][$f])) { $s->put($f, null); continue; }
            if (!array_key_exists($f, $data['fields'] ?? [])) continue;
            $v = trim((string) ($data['fields'][$f] ?? ''));
            if ($v === '' && !empty($def['secret'])) continue; // empty secret box = keep saved key
            if (!empty($def['max']) && mb_strlen($v) > $def['max']) {
                return back()->with('error', "{$def['label']} must be at most {$def['max']} characters.")->withFragment($channel);
            }
            if (!empty($def['options']) && !isset($def['options'][$v])) continue;
            $s->put($f, $v);
        }
        $s->is_active  = (bool) ($data['is_active'] ?? false);
        $s->updated_by = auth()->id();

        if ($s->is_active && !$s->isConfigured()) {
            return back()->with('error', 'Fill in all required ' . strtoupper($channel) . ' fields before switching it on.')->withFragment($channel);
        }
        $s->save();

        return back()->with('success', ucfirst($channel) . ' settings saved.')->withFragment($channel);
    }

    /** Send a real test message to the given phone/email with the saved settings. */
    public function test(Request $request, string $channel, MessagingService $messaging)
    {
        abort_unless(isset(MessagingSetting::DRIVERS[$channel]), 404);
        $to = trim((string) $request->input('to'));

        if ($channel === 'email') {
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) return response()->json(['success' => false, 'message' => 'Enter a valid email address.']);
        } else {
            $to = MessagingService::normalizePhone($to);
            if (!$to) return response()->json(['success' => false, 'message' => 'Enter a valid phone number, e.g. 08031234567.']);
        }

        $res = $messaging->send($channel, $to, 'This is a test message from the school portal. If you received it, ' . strtoupper($channel) . ' notifications are working.', [
            'name' => $request->user()->name, 'subject' => 'Test notification',
        ]);

        $driver = MessagingSetting::for($channel)->driver;
        return response()->json([
            'success' => $res['status'] === 'sent',
            'message' => match ($res['status']) {
                'sent'    => $driver === 'log' ? 'Logged only (driver is "Log only") — check storage/logs/laravel.log.' : 'Sent to ' . $to . '. Check the device/inbox.',
                'skipped' => $res['error'],
                default   => $res['error'] ?: 'Failed.',
            },
        ]);
    }
}
