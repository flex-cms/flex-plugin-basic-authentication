<?php

use Flex\Core\UI\Components\Alert;
use Flex\Core\UI\Components\Button;
use Flex\Core\UI\Components\InputField;
use Flex\Core\UI\Components\Link;
?>

<div class="flex items-center justify-center px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
    <div class="max-w-md w-full space-y-8 bg-white dark:bg-gray-800 p-10 max-sm:text-sm rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700">

        <div>
            <h2 class="text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                Регистрация
            </h2>
            <p class="mt-2 text-center text-gray-600 dark:text-gray-400">
                Създайте своя профил във Flex CMS
            </p>
        </div>

        <?= Alert::make($error ?? null); ?>

        <form class="mt-8 space-y-6" action="/auth/register" method="POST">
            <div class="rounded-md space-y-4">
                <?= InputField::make('email', 'Имейл адрес')
                    ->type('email')
                    ->placeholder('Вашият имейл адрес')
                    ->value($old['email'] ?? '')
                    ->required();
                ?>

                <?= InputField::make('password', 'Парола')
                    ->type('password')
                    ->placeholder('Въведете сигурна парола')
                    ->required();
                ?>

                <?= InputField::make('password_confirmation', 'Повторете паролата')
                    ->type('password')
                    ->placeholder('Въведете паролата отново')
                    ->required();
                ?>
            </div>

            <div>
                <?= Button::make('Регистрация')->type('submit'); ?>
            </div>

            <p class="text-center text-gray-600 dark:text-gray-400 mt-4">
                <span>Вече имате профил?</span>
                <?= Link::make('/auth/login', 'Влезте оттук'); ?>
            </p>
        </form>

    </div>
</div>
