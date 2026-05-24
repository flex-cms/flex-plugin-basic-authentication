<?php

namespace Plugins\BasicAuthentication\Controllers;

use Flex\Core\Auth;
use Flex\Core\Controllers\BaseController;
use Flex\Core\Mail\Mailer;
use Flex\Core\Routing\View;
use Flex\Core\Services\PasswordResetService;
use Flex\Models\User;

class UserController extends BaseController
{
    public function login()
    {
        $data = ['title' => 'Вход | Flex CMS'];
        $this->render(View::make('auth/login', $data));
    }

    public function register()
    {
        $data = ['title' => 'Регистрация | Flex CMS'];
        $this->render(View::make('auth/register', $data));
    }

    public function authenticate(): void
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        $duration = $_POST['remember_duration'] ?? 'month';

        if (Auth::attempt($email, $password, $remember, $duration)) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $redirectUrl = $_SESSION['redirect_url'] ?? null;
            if ($redirectUrl) {
                unset($_SESSION['redirect_url']);
                View::redirect($redirectUrl, 302);
                return;
            }

            $this->redirectByUserRole();
            return;
        }

        $data = [
            'error' => 'Невалиден имейл адрес, парола или неактивен профил!',
            'old' => ['email' => $email],
        ];

        $this->render(View::make('auth/login', $data));
    }

    public function createUser(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';

        if (empty($email) || empty($password)) {
            $data = [
                'error' => 'Всички полета са задължителни!',
                'old' => ['email' => $email]
            ];
            $this->render(View::make('auth/register', $data));
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $data = [
                'error' => 'Невалиден имейл формат!',
                'old' => ['email' => $email]
            ];
            $this->render(View::make('auth/register', $data));
            return;
        }

        if ($password !== $passwordConfirmation) {
            $data = [
                'error' => 'Паролите не съвпадат!',
                'old' => ['email' => $email]
            ];
            $this->render(View::make('auth/register', $data));
            return;
        }

        $existingEmail = User::where('email', $email)->first();
        if ($existingEmail) {
            $data = [
                'error' => 'Имейл адресът вече е зает!',
                'old' => ['email' => $email]
            ];
            $this->render(View::make('auth/register', $data));
            return;
        }

        $emailParts = explode('@', $email);
        $baseUsername = preg_replace('/[^a-zA-Z0-9_\.]/', '', $emailParts[0]);

        do {
            $username = $baseUsername . rand(100, 999);
            $usernameExists = User::where('username', $username)->exists();
        } while ($usernameExists);

        $user = User::create([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'is_active' => true
        ]);

        if ($user) {
            Auth::login($user);
            View::redirect('/admin/dashboard');
            return;
        }

        $data = [
            'error' => 'Възникна грешка при регистрацията. Моля, опитайте отново!',
            'old' => ['email' => $email]
        ];
        $this->render(View::make('auth/register', $data));
    }

    public function showForgotPassword()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $error = $_SESSION['flash_error'] ?? null;
        $status = $_SESSION['flash_status'] ?? null;

        unset($_SESSION['flash_error'], $_SESSION['flash_status']);

        $this->render(View::make('auth/forgot-password', [
            'title' => 'Забравена парола',
            'error' => $error,
            'status' => $status
        ]));
    }

    public function sendResetLink()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email = $_POST['email'] ?? '';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Моля, въведете валиден имейл адрес.';
            View::redirect('/password/reset');
        }

        try {
            $service = new PasswordResetService();
            $isSent = $service->handle($email);

            if ($isSent) {
                $_SESSION['flash_status'] = 'Линкът за възстановяване е изпратен успешно на Вашия имейл!';
            } else {
                $_SESSION['flash_error'] = 'Не е намерен потребител с този имейл адрес.';
            }

        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        View::redirect('/password/reset');
    }

    private function redirectByUserRole(): void
    {
        if (Auth::isAdmin()) {
            View::redirect('/admin/dashboard');
        } else {
            View::redirect('/');
        }
    }
}
