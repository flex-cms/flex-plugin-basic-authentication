<?php

namespace Plugins\BasicAuthentication\Controllers;

use Flex\Models\PasswordReset;
use Flex\Models\User;
use InvalidArgumentException;
use Exception;
use Flex\Core\Auth;
use Flex\Core\Controllers\BaseController;
use Flex\Core\Routing\View;
use Plugins\BasicAuthentication\Services\PasswordResetService;
use Plugins\BasicAuthentication\Services\UserService;

class UserController extends BaseController
{
    protected UserService $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function createUser(): void
    {
        $email = trim($_POST['email'] ?? '');

        try {
            $user = $this->userService->register($_POST);

            Auth::login($user);
            $this->redirectByUserRole();
        } catch (InvalidArgumentException $e) {
            $error = $e->getMessage();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $this->render(View::make('auth/register', [
            'error' => $error,
            'old' => ['email' => $email]
        ]));
    }

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
            }

            $this->redirectByUserRole();
        }

        $data = [
            'error' => 'Невалиден имейл адрес, парола или неактивен профил!',
            'old' => ['email' => $email],
        ];

        $this->render(View::make('auth/login', $data));
    }

    public function forgot()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $error = $_SESSION['flash_error'] ?? null;
        $status = $_SESSION['flash_success'] ?? null;

        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

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
            View::redirect('/password/forgot');
        }

        try {
            $service = new PasswordResetService();
            $isSent = $service->handle($email);

            if ($isSent) {
                $_SESSION['flash_success'] = 'Линкът за възстановяване е изпратен успешно на Вашия имейл!';
            } else {
                $_SESSION['flash_error'] = 'Не е намерен потребител с този имейл адрес.';
            }

        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        View::redirect('/password/forgot');
    }

    public function reset()
    {
        $token = $_GET['token'] ?? null;

        if (!$token) {
            $_SESSION['flash_error'] = 'Липсва токен за възстановяване.';
            View::redirect('/password/forgot');
        }

        $resetRecord = PasswordReset::where('token', $token)->first();

        if (!$resetRecord || $resetRecord->isExpired()) {
            $_SESSION['flash_error'] = 'Токенът е невалиден или вече е изтекъл.';
            View::redirect('/password/forgot');
        }

        $this->render(View::make('auth/reset-password', [
            'title' => 'Промяна на парола',
            'token' => $token
        ]));
    }

    public function change()
    {
        $token = $_POST['token'] ?? null;
        $password = $_POST['password'] ?? null;
        $passwordConfirm = $_POST['password_confirm'] ?? null;

        if (!$token || !$password || $password !== $passwordConfirm) {
            $this->render(View::make('auth/reset-password', [
                'title' => 'Промяна на парола',
                'token' => $token,
                'error' => 'Моля, попълнете правилно всички полета.'
            ]));
        }

        $resetRecord = PasswordReset::where('token', $token)->first();

        if (!$resetRecord || $resetRecord->isExpired()) {
            return $this->render(View::make('auth/forgot-password', [
                'title' => 'Забравена парола',
                'error' => 'Токенът е невалиден или изтекъл.'
            ], 'auth'));
        }

        $user = User::where('email', $resetRecord->email)->first();

        if (!$user) {
            return $this->render(View::make('auth/forgot-password', [
                'title' => 'Забравена парола',
                'error' => 'Потребителят не е намерен.'
            ], 'auth'));
        }

        $user->update([
            'password' => $password
        ]);

        PasswordReset::deleteExistingForEmail($resetRecord->email);

        $_SESSION['flash_success'] = 'Паролата ви е променена успешно. Можете да влезете в профила си.';
        View::redirect('/auth/login');
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