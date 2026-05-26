<?php

namespace Plugins\BasicAuthentication\Services;

use Flex\Core\Mail\Mailer;
use Flex\Models\PasswordReset;
use Flex\Models\User;
use Flex\Core\Utils\TimeHelper;
use PHPMailer\PHPMailer\Exception;
use Plugins\EmailTemplates\Models\EmailTemplate;

class PasswordResetService
{
    public function handle(string $email): bool
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            throw new Exception("Не е намерен потребител с този имейл адрес.");
        }

        $lastReset = PasswordReset::where('email', $email)
            ->where('created_at', '>=', gmdate('Y-m-d H:i:s', strtotime('-5 minutes')))
            ->orderBy('created_at', 'DESC')
            ->first();

        if ($lastReset) {
            $elapsed = TimeHelper::elapsedSeconds($lastReset->created_at);
            $remainingSeconds = 300 - $elapsed;

            if ($remainingSeconds > 0) {
                $minutes = floor($remainingSeconds / 60);
                $seconds = $remainingSeconds % 60;
                throw new Exception("Моля, изчакайте още {$minutes} минута(и) и {$seconds} секунди.");
            }
        }

        $template = EmailTemplate::where('slug', 'auth.password_reset')->first();

        if (!$template) {
            throw new Exception("Шаблонът за парола не е намерен.");
        }

        PasswordReset::where('email', $email)->delete();

        $token = bin2hex(random_bytes(32));
        PasswordReset::create([
            'email' => $email,
            'token' => $token,
            'created_at' => TimeHelper::nowUtc(),
            'expires_at' => TimeHelper::addTime('+1 hour')
        ]);

        $baseUrl = $_ENV['APP_URL'] ?? 'https://kriskata.com';
        $resetLink = $baseUrl . "/password/change?token=" . $token;

        $isSent = Mailer::to($email)
            ->subject($template->subject)
            ->body($template->body)
            ->withVariables([
                'user_name' => $user->fullname ?? 'Fullname',
                'reset_url' => $resetLink
            ])
            ->send();

        return $isSent;
    }
}