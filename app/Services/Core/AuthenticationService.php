<?php declare(strict_types=1);

namespace App\Services\Core;

use App\Models\User;
use Illuminate\Auth\AuthManager;

class AuthenticationService
{
    public function __construct(
        private AuthManager $auth,
    ) {
    }

    public function attempt(array $credentials): bool
    {
        return $this->auth->attempt($credentials);
    }

    public function login(User $user): void
    {
        $this->auth->login($user);
    }

    public function logout(): void
    {
        $this->auth->logout();
    }

    public function user(): ?User
    {
        return $this->auth->user();
    }

    public function check(): bool
    {
        return $this->auth->check();
    }
}
