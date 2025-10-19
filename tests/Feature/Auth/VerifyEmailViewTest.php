<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class VerifyEmailViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_user_sees_simplified_verify_view(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'fan@example.com',
        ]);

        $this->actingAs($user)
            ->get(route('verification.notice'))
            ->assertOk()
            ->assertSee('Verify your email address')
            ->assertSee('favourite') // British English copy
            ->assertSee('fan@example.com')
            ->assertSee('Resend verification email')
            ->assertSee('Update email address')
            ->assertSee('Sign out')
            ->assertSee('support@hd-tickets.com')
            ->assertDontSee('Continue without verifying');
    }

    public function test_success_banner_shows_when_verification_link_sent(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->withSession(['status' => 'verification-link-sent'])
            ->get(route('verification.notice'))
            ->assertOk()
            ->assertSee('A new verification email has been sent');
    }
}
