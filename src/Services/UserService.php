<?php

namespace Plugins\BasicAuthentication\Services;

use Flex\Models\User;
use Flex\Models\Role;
use InvalidArgumentException;
use Exception;

class UserService
{
    public function register(array $data): User
    {
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $passwordConfirmation = $data['password_confirmation'] ?? '';

        if (empty($email) || empty($password)) {
            throw new InvalidArgumentException('Всички полета са задължителни!');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Невалиден имейл формат!');
        }

        if ($password !== $passwordConfirmation) {
            throw new InvalidArgumentException('Паролите не съвпадат!');
        }

        if (User::where('email', $email)->exists()) {
            throw new InvalidArgumentException('Имейл адресът вече е зает!');
        }

        $username = $this->generateUniqueUsername($email);

        $user = User::create([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'is_active' => true
        ]);

        if (!$user) {
            throw new Exception('Възникна грешка при регистрацията. Моля, опитайте отново!');
        }

        $defaultRole = Role::where('is_default', true)->first();
        if ($defaultRole) {
            if (method_exists($user, 'roles')) {
                $user->roles()->attach($defaultRole->id);
            }
        }

        return $user;
    }

    private function generateUniqueUsername(string $email): string
    {
        $emailParts = explode('@', $email);
        $baseUsername = preg_replace('/[^a-zA-Z0-9_\.]/', '', $emailParts[0]);

        do {
            $username = $baseUsername . rand(100, 999);
            $usernameExists = User::where('username', $username)->exists();
        } while ($usernameExists);

        return $username;
    }
}
