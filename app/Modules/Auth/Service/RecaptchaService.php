<?php

namespace App\Modules\Auth\Service;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class RecaptchaService
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * @throws ValidationException
     * @throws RuntimeException
     */
    public function verify(string $token, ?string $ip = null): void
    {
        $secret = (string) config('services.recaptcha.secret_key', '');

        if (trim($secret) === '') {
            throw new RuntimeException('reCAPTCHA is not configured.');
        }

        $payload = [
            'secret' => $secret,
            'response' => $token,
        ];

        if (!empty($ip)) {
            $payload['remoteip'] = $ip;
        }

        $response = Http::asForm()->post(self::VERIFY_URL, $payload);

        if (!$response->ok()) {
            throw new RuntimeException('reCAPTCHA verification service is unavailable.');
        }

        $data = $response->json();
        $isValid = (bool) ($data['success'] ?? false);

        $expectedHostname = trim((string) config('services.recaptcha.expected_hostname', ''));
        $hostname = (string) ($data['hostname'] ?? '');

        if ($expectedHostname !== '' && $hostname !== '' && strcasecmp($hostname, $expectedHostname) !== 0) {
            throw ValidationException::withMessages([
                'g-recaptcha-response' => ['Invalid reCAPTCHA hostname. Please retry.'],
            ]);
        }

        if (!$isValid) {
            throw ValidationException::withMessages([
                'g-recaptcha-response' => ['reCAPTCHA validation failed. Please try again.'],
            ]);
        }
    }
}