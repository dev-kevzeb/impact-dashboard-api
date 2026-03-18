<?php

namespace Tests\Feature;

use App\Modules\Role\Domain\Role;
use App\Modules\User\Domain\User;
use App\Modules\UserState\Domain\UserState;
use App\Notifications\EmailVerifiedAwaitingApprovalNotification;
use App\Notifications\PendingRegistrationForAdminNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class VerificationAdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\UserStateSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_email_verification_notifies_active_admins_about_pending_request(): void
    {
        Notification::fake();

        $activeState = UserState::where('name', 'active')->firstOrFail();
        $unverifiedState = UserState::where('name', 'unverified')->firstOrFail();
        $adminRole = Role::where('name', 'admin')->firstOrFail();
        $projectManagerRole = Role::where('name', 'project-manager')->firstOrFail();

        $admin = User::factory()->create([
            'name' => 'Admin Reviewer',
            'email' => 'admin-reviewer@test.com',
            'user_state_id' => $activeState->id,
        ]);
        $admin->roles()->attach($adminRole->id);

        $applicant = User::factory()->create([
            'name' => 'Pending Applicant',
            'email' => 'pending-applicant@test.com',
            'user_state_id' => $unverifiedState->id,
            'email_verified_at' => null,
        ]);
        $applicant->roles()->attach($projectManagerRole->id);

        $hash = sha1($applicant->email);

        $response = $this->getJson("/api/v1/email/verify/{$applicant->id}/{$hash}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Email verified successfully',
            ]);

        $pendingState = UserState::where('name', 'pending')->firstOrFail();
        $this->assertDatabaseHas('user', [
            'id' => $applicant->id,
            'user_state_id' => $pendingState->id,
        ]);

        Notification::assertSentTo(
            $admin,
            PendingRegistrationForAdminNotification::class
        );

        Notification::assertSentTo(
            $applicant,
            EmailVerifiedAwaitingApprovalNotification::class
        );
    }

    public function test_already_verified_email_does_not_send_notifications_again(): void
    {
        Notification::fake();

        $activeState = UserState::where('name', 'active')->firstOrFail();
        $pendingState = UserState::where('name', 'pending')->firstOrFail();
        $adminRole = Role::where('name', 'admin')->firstOrFail();
        $projectManagerRole = Role::where('name', 'project-manager')->firstOrFail();

        $admin = User::factory()->create([
            'name' => 'Admin Reviewer',
            'email' => 'admin-reviewer-2@test.com',
            'user_state_id' => $activeState->id,
        ]);
        $admin->roles()->attach($adminRole->id);

        $applicant = User::factory()->create([
            'name' => 'Already Verified Applicant',
            'email' => 'already-verified-applicant@test.com',
            'user_state_id' => $pendingState->id,
            'email_verified_at' => now(),
        ]);
        $applicant->roles()->attach($projectManagerRole->id);

        $hash = sha1($applicant->email);

        $response = $this->getJson("/api/v1/email/verify/{$applicant->id}/{$hash}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Email already verified',
            ]);

        $this->assertDatabaseHas('user', [
            'id' => $applicant->id,
            'user_state_id' => $pendingState->id,
        ]);

        Notification::assertNothingSent();
    }

    public function test_resend_verification_sends_email_for_unverified_user(): void
    {
        Notification::fake();

        $unverifiedState = UserState::where('name', 'unverified')->firstOrFail();

        $user = User::factory()->create([
            'name' => 'Unverified User',
            'email' => 'unverified-user@test.com',
            'user_state_id' => $unverifiedState->id,
            'email_verified_at' => null,
        ]);

        $response = $this->postJson('/api/v1/email/resend', [
            'email' => $user->email,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Verification email resent successfully',
            ]);

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_resend_verification_returns_error_for_verified_user(): void
    {
        Notification::fake();

        $activeState = UserState::where('name', 'active')->firstOrFail();

        $user = User::factory()->create([
            'name' => 'Verified User',
            'email' => 'verified-user@test.com',
            'user_state_id' => $activeState->id,
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/email/resend', [
            'email' => $user->email,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Email already verified',
            ]);

        Notification::assertNothingSent();
    }

    public function test_email_verification_notifies_configured_admin_mailbox(): void
    {
        Notification::fake();
        config(['mail.admin_notification_emails' => ['admpacificecommerce@gmail.com']]);

        $unverifiedState = UserState::where('name', 'unverified')->firstOrFail();
        $projectManagerRole = Role::where('name', 'project-manager')->firstOrFail();

        $applicant = User::factory()->create([
            'name' => 'Applicant With Configured Admin Mailbox',
            'email' => 'applicant-config-mailbox@test.com',
            'user_state_id' => $unverifiedState->id,
            'email_verified_at' => null,
        ]);
        $applicant->roles()->attach($projectManagerRole->id);

        $hash = sha1($applicant->email);

        $response = $this->getJson("/api/v1/email/verify/{$applicant->id}/{$hash}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Email verified successfully',
            ]);

        Notification::assertSentOnDemand(
            PendingRegistrationForAdminNotification::class,
            function ($notification, $channels, $notifiable): bool {
                if (!in_array('mail', $channels, true)) {
                    return false;
                }

                $mailRoute = $notifiable->routes['mail'] ?? null;

                return is_string($mailRoute) && $mailRoute === 'admpacificecommerce@gmail.com';
            }
        );
    }
}