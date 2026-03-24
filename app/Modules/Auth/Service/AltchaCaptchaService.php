<?php

namespace App\Modules\Auth\Service;

use AltchaOrg\Altcha\Altcha;
use AltchaOrg\Altcha\ChallengeOptions;
use DateInterval;
use DateTimeImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class AltchaCaptchaService
{
    private const REPLAY_CACHE_PREFIX = 'altcha:used:';

    public function createChallenge(): array
    {
        $altcha = $this->buildClient();

        $expires = (new DateTimeImmutable())->add(new DateInterval('PT' . $this->expiresInSeconds() . 'S'));
        $challenge = $altcha->createChallenge(new ChallengeOptions(
            maxNumber: $this->maxNumber(),
            expires: $expires,
        ));

        return [
            'algorithm' => $challenge->algorithm,
            'challenge' => $challenge->challenge,
            'salt' => $challenge->salt,
            'signature' => $challenge->signature,
            // ALTCHA widget expects the `maxnumber` key.
            'maxnumber' => $challenge->maxNumber,
        ];
    }

    /**
     * @throws RuntimeException
     * @throws ValidationException
     */
    public function assertValidPayload(string $payload): void
    {
        $altcha = $this->buildClient();
        $replayCacheKey = self::REPLAY_CACHE_PREFIX . hash('sha256', $payload);

        if (Cache::has($replayCacheKey)) {
            throw ValidationException::withMessages([
                'altcha' => ['Captcha already used. Please retry.'],
            ]);
        }

        if (!$altcha->verifySolution($payload, true)) {
            throw ValidationException::withMessages([
                'altcha' => ['Captcha validation failed. Please try again.'],
            ]);
        }

        Cache::put($replayCacheKey, true, now()->addSeconds($this->expiresInSeconds()));
    }

    /**
     * @throws RuntimeException
     */
    private function buildClient(): Altcha
    {
        $hmacKey = (string) config('services.altcha.hmac_key', '');

        if (trim($hmacKey) === '') {
            throw new RuntimeException('ALTCHA is not configured.');
        }

        return new Altcha($hmacKey);
    }

    private function maxNumber(): int
    {
        return max(1, (int) config('services.altcha.max_number', 100000));
    }

    private function expiresInSeconds(): int
    {
        return max(60, (int) config('services.altcha.expire_seconds', 300));
    }
}
