<?php

use Flex\Core\UI\Components\Alert;
use Flex\Core\UI\Components\Button;
use Flex\Core\UI\Components\InputField;
use Flex\Core\UI\Components\Link;
use Flex\Core\UI\Form;
?>

<div class="flex items-center justify-center px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
    <div class="max-w-md w-full space-y-8 bg-white dark:bg-gray-800 p-10 max-sm:text-sm rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700">

        <div>
            <h2 class="text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                Вход за клиенти
            </h2>
            <p class="mt-2 text-center text-gray-600 dark:text-gray-400">
                Добре дошли в купуваческия портал на Flex CMS
            </p>
        </div>

        <?php if (!empty($error)): ?>
            <?= Alert::make($error)->error(); ?>
        <?php endif; ?>

        <?php if (!empty($status)): ?>
            <?= Alert::make($status)->success(); ?>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="/auth/login" method="POST">
            <div class="rounded-md space-y-4">
                <?= InputField::make('email', 'Имейл адрес')
                    ->type('email')
                    ->placeholder('Имейл адресът, с който сте регистрирани.')
                    ->value($old['email'] ?? '')
                    ->required();
                ?>

                <?= InputField::make('password', 'Парола')
                    ->type('password')
                    ->placeholder('Вашата паролата')
                    ->required();
                ?>
            </div>

            <div class="flex items-center justify-between">
                <?php Form::toggle('remember', 'Запомни ме', [
                    'value' => false
                ]); ?>

                <?= Link::make('/password/forgot', 'Забравена парола?'); ?>
            </div>

            <div>
                <?= Button::make('Вход в профила')->type('submit'); ?>
            </div>

            <p class="text-center text-gray-600 dark:text-gray-400 mt-4">
                <span>Нямате профил?</span>
                <?= Link::make('/auth/register', 'Регистрирайте се тук'); ?>
            </p>
        </form>

    </div>
</div>
