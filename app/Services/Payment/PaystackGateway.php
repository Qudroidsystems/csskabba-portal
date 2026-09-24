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
        $gw = PaymentGateway::where('provider_key', 'paystack')->first();

        if ($gw) {
            $this->active = (bool) $gw->is_active;
            $this->mode   = $gw->mode === 'live' ? 'live' : 'sandbox';
            $config       = $gw->config ?? [];
            $this->secretKey = $this->mode === 'live' ? $gw->secret_key : ($config['test_secret_key'] ?? $gw->secret_key);
            $this->publicKey = $this->mode === 'live' ? $gw->public_key : ($config['test_public_key'] ?? $gw->public_key);
        }

        if (!$this->secretKey && env('PAYSTACK_SECRET_KEY')) {
            $this->secretKey = env('PAYSTACK_SECRET_KEY');
            $this->publicKey = env('PAYSTACK_PUBLIC_KEY');
            $this->active    = true;
            $this->mode      = str_starts_with((string) $this->secretKey, 'sk_live_') ? 'live' : 'sandbox';
        }
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
