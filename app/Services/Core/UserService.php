<?php declare(strict_types=1);

namespace App\Services\Core;

use App\Models\User;

class UserService
{
    public function createUser(array $userData): User
    {
        return User::create($userData);
    }

    public function getUserById(int $id): ?User
    {
        return User::find($id);
    }

    public function updateUser(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function deleteUser(User $user): bool
    {
        return $user->delete();
    }

    public function getUsersByRole(string $role): \Illuminate\Database\Eloquent\Collection
    {
        return User::where('role', $role)->get();
    }
}
