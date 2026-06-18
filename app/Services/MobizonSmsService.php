<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\SmsBalanceLowMail;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MobizonSmsService
{
    private string $apiKey;

    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.mobizon.api_key', '');
        $this->apiUrl = config('services.mobizon.api_url', 'https://api.mobizon.kz/service');
    }

    /**
     * Send SMS message to phone number.
     */
    public function send(string $phone, string $message): bool
    {
        if (empty($this->apiKey)) {
            Log::warning('Mobizon API key not configured');

            return false;
        }

        try {
            $response = $this->client()->post('/Message/SendSmsMessage', [
                'recipient' => $this->normalizePhone($phone),
                'text' => $message,
            ]);

            $data = $response->json();

            if ($response->successful() && ($data['code'] ?? null) === 0) {
                Log::info('SMS sent successfully', ['phone' => $phone]);

                return true;
            }

            Log::error('Failed to send SMS', [
                'phone' => $phone,
                'response' => $data,
            ]);

            $this->notifyAdminIfBalanceLow($phone, $data['message'] ?? 'Unknown error');

            return false;
        } catch (\Exception $e) {
            Log::error('SMS sending exception', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send verification code to phone number.
     */
    public function sendVerificationCode(string $phone, string $code): bool
    {
        $message = __('sms.verification_code', ['code' => $code]);

        return $this->send($phone, $message);
    }

    /**
     * Fetch the current account balance (in the account currency, e.g. KZT).
     * Returns null when the key is missing or the request fails.
     */
    public function getBalance(): ?float
    {
        if (empty($this->apiKey)) {
            return null;
        }

        try {
            $response = $this->client()->get('/User/GetOwnBalance');
            $data = $response->json();

            if ($response->successful() && ($data['code'] ?? null) === 0) {
                return (float) ($data['data']['balance'] ?? 0);
            }

            Log::warning('Failed to fetch Mobizon balance', ['response' => $data]);

            return null;
        } catch (\Throwable $e) {
            Log::warning('Mobizon balance check failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Generate a random verification code.
     */
    public function generateCode(int $length = 6): string
    {
        return str_pad((string) random_int(0, (int) str_repeat('9', $length)), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Notify admin via email when SMS fails (e.g. insufficient balance).
     */
    private function notifyAdminIfBalanceLow(string $phone, string $errorMessage): void
    {
        $adminEmail = config('services.mobizon.admin_email');

        if (empty($adminEmail)) {
            Log::warning('ADMIN_EMAIL not configured — cannot send SMS failure alert');

            return;
        }

        try {
            Mail::to($adminEmail)->queue(new SmsBalanceLowMail($phone, $errorMessage));
            Log::info('SMS failure alert sent to admin', ['email' => $adminEmail]);
        } catch (\Exception $e) {
            Log::error('Failed to send SMS failure alert email', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Normalize phone number (remove spaces, dashes, etc.).
     */
    private function normalizePhone(string $phone): string
    {
        return preg_replace('/[^0-9+]/', '', $phone) ?? $phone;
    }

    /**
     * Get HTTP client with base configuration.
     */
    private function client(): PendingRequest
    {
        return Http::baseUrl($this->apiUrl)
            ->withQueryParameters(['apiKey' => $this->apiKey])
            ->timeout(30);
    }
}
