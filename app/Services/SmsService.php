<?php

namespace App\Services;

use Vonage\Client;
use Vonage\Client\Credentials\Basic;
use Vonage\SMS\Message\SMS;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected Client $client;
    protected string $fromNumber;

    public function __construct()
    {
        $apiKey = config('services.vonage.key');
        $apiSecret = config('services.vonage.secret');
        $this->fromNumber = config('services.vonage.sms_from');

        if (!$apiKey || !$apiSecret || !$this->fromNumber) {
            throw new \Exception('Vonage credentials not configured. Set VONAGE_KEY, VONAGE_SECRET, and VONAGE_SMS_FROM in .env');
        }

        $this->client = new Client(new Basic($apiKey, $apiSecret));
    }

    /**
     * Send a single SMS message.
     */
    public function sendSms(string $to, string $body): string
    {
        $response = $this->client->sms()->send(new SMS($to, $this->fromNumber, $body));
        $message = $response->current();

        if ($message->getStatus() != 0) {
            throw new \Exception('SMS failed: ' . $message->getErrorText());
        }

        return $message->getMessageId();
    }

    /**
     * Send SMS to multiple recipients.
     * Returns array with 'sent' count and 'failed' array of [number => error].
     */
    public function sendBulkSms(array $recipients, string $body): array
    {
        $sent = 0;
        $failed = [];

        foreach ($recipients as $number) {
            try {
                $this->sendSms($number, $body);
                $sent++;
            } catch (\Exception $e) {
                $failed[$number] = $e->getMessage();
                Log::warning("SMS failed to {$number}: " . $e->getMessage());
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }

    /**
     * Format a phone number to E.164 format for US numbers.
     * Strips non-digits, prepends +1 if needed.
     * Returns null if the number is clearly invalid.
     */
    public static function formatPhoneNumber(string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (strlen($digits) === 10) {
            return '+1' . $digits;
        }

        if (strlen($digits) === 11 && $digits[0] === '1') {
            return '+' . $digits;
        }

        if (strlen($digits) >= 11) {
            return '+' . $digits;
        }

        return null;
    }
}
