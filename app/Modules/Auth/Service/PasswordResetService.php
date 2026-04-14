<?php

namespace App\Modules\Auth\Service;

use App\Modules\User\Domain\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use RuntimeException;

class PasswordResetService
{
    public function sendResetLink(string $email): void
    {
        Password::broker()->sendResetLink([
            'email' => $email,
        ]);
    }

    public function resetPassword(string $token, string $email, string $password): void
    {
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$resetRecord || !Hash::check($token, $resetRecord->token)) {
            throw new RuntimeException('Unable to reset password. The token is invalid or has expired.');
        }

        $expiryMinutes = (int) config('auth.passwords.users.expire', 60);
        $createdAt = Carbon::parse($resetRecord->created_at);

        if ($createdAt->addMinutes($expiryMinutes)->isPast()) {
            throw new RuntimeException('Unable to reset password. The token is invalid or has expired.');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            throw new RuntimeException('Unable to reset password. The token is invalid or has expired.');
        }

        $user->password = $password;
        $user->save();

        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->delete();
    }
}