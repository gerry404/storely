<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FlutterwaveService
{
    private string $baseUrl = 'https://api.flutterwave.com/v3';
    private string $secretKey;
    private string $publicKey;

    public function __construct()
    {
        $this->secretKey = config('services.flutterwave.secret_key', '');
        $this->publicKey = config('services.flutterwave.public_key', '');
    }

    private function http()
    {
        $client = Http::withToken($this->secretKey);

        // Fix for misconfigured curl.cainfo (common on Laragon/Windows)
        $caPaths = [
            ini_get('curl.cainfo'),
            'C:\\laragon\\etc\\ssl\\cacert.pem',
            '/etc/ssl/certs/ca-certificates.crt',
        ];
        foreach ($caPaths as $p) {
            if ($p && file_exists($p)) {
                $client = $client->withOptions(['verify' => $p]);
                break;
            }
        }

        return $client;
    }

    /**
     * Initialize a payment (Standard approach).
     */
    public function initializePayment(array $data): array
    {
        $txRef = 'STR-' . strtoupper(Str::random(16)) . '-' . time();

        $payload = [
            'tx_ref' => $txRef,
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'XAF',
            'redirect_url' => $data['redirect_url'] ?? config('app.frontend_url', 'http://localhost:5173') . '/payment/callback',
            'customer' => [
                'email' => $data['email'],
                'name' => $data['name'] ?? '',
                'phonenumber' => $data['phone'] ?? '',
            ],
            'customizations' => [
                'title' => 'Storely',
                'description' => $data['description'] ?? 'Paiement Storely',
                'logo' => config('app.url') . '/logo.png',
            ],
            'meta' => $data['meta'] ?? [],
            'payment_options' => $data['payment_options'] ?? 'mobilemoneyfranco,card',
        ];

        $response = $this->http()->post("{$this->baseUrl}/payments", $payload);

        if ($response->successful() && $response->json('status') === 'success') {
            return [
                'success' => true,
                'tx_ref' => $txRef,
                'payment_link' => $response->json('data.link'),
            ];
        }

        return [
            'success' => false,
            'message' => $response->json('message', 'Erreur lors de l\'initialisation du paiement'),
            'tx_ref' => $txRef,
        ];
    }

    /**
     * Get public key for frontend inline checkout.
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * Verify a transaction by its ID.
     */
    public function verifyTransaction(string $transactionId): array
    {
        $response = $this->http()
            ->get("{$this->baseUrl}/transactions/{$transactionId}/verify");

        if ($response->successful() && $response->json('status') === 'success') {
            $data = $response->json('data');
            return [
                'success' => true,
                'status' => $data['status'],
                'tx_ref' => $data['tx_ref'],
                'flw_ref' => $data['flw_ref'] ?? null,
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'payment_type' => $data['payment_type'] ?? null,
                'data' => $data,
            ];
        }

        return [
            'success' => false,
            'message' => $response->json('message', 'Vérification échouée'),
        ];
    }

    /**
     * Verify a transaction by tx_ref (alternative).
     */
    public function verifyByTxRef(string $txRef): array
    {
        $response = $this->http()
            ->get("{$this->baseUrl}/transactions/verify_by_reference", [
                'tx_ref' => $txRef,
            ]);

        if ($response->successful() && $response->json('status') === 'success') {
            $data = $response->json('data');
            return [
                'success' => true,
                'status' => $data['status'],
                'tx_ref' => $data['tx_ref'],
                'flw_ref' => $data['flw_ref'] ?? null,
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'payment_type' => $data['payment_type'] ?? null,
                'data' => $data,
            ];
        }

        return [
            'success' => false,
            'message' => $response->json('message', 'Vérification échouée'),
        ];
    }

    /**
     * Validate webhook signature.
     */
    public static function validateWebhookSignature(string $signature): bool
    {
        $secretHash = config('services.flutterwave.webhook_hash', '');
        return $secretHash && hash_equals($secretHash, $signature);
    }
}
