<?php

namespace Tests\Feature;

use App\Modules\User\Domain\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_update_own_profile_name(): void
    {
        $headers = $this->authHeaders('project-manager');

        $response = $this->putJson('/api/v1/auth/profile', [
            'name' => 'Updated Profile Name',
        ], $headers);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Updated Profile Name');

        $user = User::where('email', 'test@pacific.com')->firstOrFail();
        $this->assertSame('Updated Profile Name', $user->name);
    }

    public function test_profile_update_rejects_prohibited_fields(): void
    {
        $headers = $this->authHeaders('project-manager');

        $response = $this->putJson('/api/v1/auth/profile', [
            'name' => 'Valid Name',
            'email' => 'hacker@example.com',
        ], $headers);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_can_change_password_with_correct_current_password(): void
    {
        $headers = $this->authHeaders('project-manager');

        $response = $this->postJson('/api/v1/auth/change-password', [
            'current_password' => 'password123',
            'password' => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ], $headers);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Password updated successfully');

        $user = User::where('email', 'test@pacific.com')->firstOrFail();
        $this->assertTrue(Hash::check('newpassword456', $user->password));
    }

    public function test_change_password_returns_validation_error_when_current_password_is_incorrect(): void
    {
        $headers = $this->authHeaders('project-manager');

        $response = $this->postJson('/api/v1/auth/change-password', [
            'current_password' => 'wrong-password',
            'password' => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ], $headers);

        $response
            ->assertStatus(422)
            ->assertJsonPath('errors.current_password.0', 'Current password is incorrect.');
    }
}
