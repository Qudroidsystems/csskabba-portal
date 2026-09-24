<?php

namespace App\Services\Payment;

use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin Paystack client for school-fee checkouts.
 *
 * Keys come from Finance › Payment Gateways (payment_gateways row with
 * provider_key "paystack": live keys in secret_key/public_key, test keys in
 * config.test_secret_key / config.test_public_key), falling back to
 * PAYSTACK_SECRET_KEY / PAYSTACK_PUBLIC_KEY in .env.
 */
class PaystackGateway
{
    protected const BASE_URL = 'https://api.paystack.co';

    protected ?string $secretKey = null;
    protected ?string $publicKey = null;
    protected string $mode = 'sandbox';
    protected bool $active = false;

    public function __construct()
    {
        $gw     = PaymentGateway::where('provider_key', 'paystack')->first();
        $env    = (array) config('services.paystack', []);
        $config = (array) ($gw->config ?? []);

        $this->mode = ($gw->mode ?? ($env['mode'] ?? 'sandbox')) === 'live' ? 'live' : 'sandbox';

        // First key that is real (not a seeder placeholder like "sk_test_xxxx…")
        // and matches the mode. Admin settings win over .env.
        if ($this->mode === 'live') {
            $secrets = [$gw->secret_key ?? null, $config['live_secret_key'] ?? null, $env['live_secret_key'] ?? null, $env['secret_key'] ?? null];
            $publics = [$gw->public_key ?? null, $config['live_public_key'] ?? null, $env['live_public_key'] ?? null, $env['public_key'] ?? null];
        } else {
            $secrets = [$config['test_secret_key'] ?? null, $gw->secret_key ?? null, $env['test_secret_key'] ?? null, $env['secret_key'] ?? null];
            $publics = [$config['test_public_key'] ?? null, $gw->public_key ?? null, $env['test_public_key'] ?? null, $env['public_key'] ?? null];
        }
        $prefix = $this->mode === 'live' ? 'live' : 'test';
        $this->secretKey = $this->firstRealKey($secrets, 'sk_' . $prefix . '_');
        $this->publicKey = $this->firstRealKey($publics, 'pk_' . $prefix . '_');

        // Active when the admin switched it on, or (no gateway row) when .env says so.
        $this->active = $gw ? (bool) $gw->is_active : filter_var($env['active'] ?? true, FILTER_VALIDATE_BOOLEAN);
    }

    protected function firstRealKey(array $candidates, string $prefix): ?string
    {
        foreach ($candidates as $key) {
            $key = trim((string) $key);
            if (str_starts_with($key, $prefix) && strlen($key) >= 30 && !str_contains(strtolower($key), 'xxxx')) {
                return $key;
            }
        }
        return null;
    }

    /** Why the gateway can't be used (for admins), or null when it's ready. */
    public function problem(): ?string
    {
        if (!$this->active) return 'Paystack is switched off in Payment Gateways.';
        if (!$this->secretKey) return 'No valid Paystack ' . ($this->mode === 'live' ? 'live (sk_live_…)' : 'test (sk_test_…)') . ' secret key is set.';
        return null;
    }

    public function isReady(): bool
    {
        return $this->active && !empty($this->secretKey);
    }

    public function mode(): string
    {
        return $this->mode;
    }

    /**
     * @return array{ok: bool, authorization_url?: string, access_code?: string, message?: string}
     */
    public function initialize(string $email, int $amountKobo, string $reference, string $callbackUrl, array $metadata = []): array
    {
        if (!$this->secretKey) {
            return ['ok' => false, 'message' => 'Online payment is not configured.'];
        }
        try {
            $res = Http::withToken($this->secretKey)->acceptJson()->timeout(30)
                ->post(self::BASE_URL . '/transaction/initialize', [
                    'email'        => $email,
                    'amount'       => $amountKobo,
                    'currency'     => 'NGN',
                    'reference'    => $reference,
                    'callback_url' => $callbackUrl,
                    'metadata'     => $metadata,
                ]);

            if ($res->successful() && $res->json('status') === true) {
                return [
                    'ok'                => true,
                    'authorization_url' => $res->json('data.authorization_url'),
                    'access_code'       => $res->json('data.access_code'),
                ];
            }

            Log::warning('Paystack initialize failed', ['reference' => $reference, 'body' => $res->json()]);
            return ['ok' => false, 'message' => $res->json('message') ?: 'Could not start the payment.'];
        } catch (\Throwable $e) {
            Log::error('Paystack initialize error', ['reference' => $reference, 'error' => $e->getMessage()]);
            return ['ok' => false, 'message' => 'The payment service could not be reached. Please try again.'];
        }
    }

    /**
     * @return array{ok: bool, data?: array, message?: string}  ok = the API answered (not that the payment succeeded)
     */
    public function verify(string $reference): array
    {
        if (!$this->secretKey) {
            return ['ok' => false, 'message' => 'Online payment is not configured.'];
        }
        try {
            $res = Http::withToken($this->secretKey)->acceptJson()->timeout(30)
                ->get(self::BASE_URL . '/transaction/verify/' . rawurlencode($reference));

            if ($res->successful() && $res->json('status') === true) {
                return ['ok' => true, 'data' => (array) $res->json('data')];
            }

            return ['ok' => false, 'message' => $res->json('message') ?: 'Verification failed.'];
        } catch (\Throwable $e) {
            Log::error('Paystack verify error', ['reference' => $reference, 'error' => $e->getMessage()]);
            return ['ok' => false, 'message' => 'The payment service could not be reached.'];
        }
    }

    public function validSignature(string $payload, ?string $signature): bool
    {
        if (!$signature || !$this->secretKey) {
            return false;
        }
        return hash_equals(hash_hmac('sha512', $payload, $this->secretKey), $signature);
    }
}
