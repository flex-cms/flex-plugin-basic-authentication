<?php

use Flex\Core\Middlewares\GuestMiddleware;
use Plugins\BasicAuthentication\Controllers\UserController;

$eventManager->listen('router.register', function ($router) {
    $router->get('/auth/login', [UserController::class, 'login'], [GuestMiddleware::class]);
    $router->get('/auth/register', [UserController::class, 'register'], [GuestMiddleware::class]);
    $router->post('/auth/login', [UserController::class, 'authenticate'], [GuestMiddleware::class]);
    $router->post('/auth/register', [UserController::class, 'createUser'], [GuestMiddleware::class]);
    $router->get('/password/reset', [UserController::class, 'showForgotPassword'], [GuestMiddleware::class]);
    $router->post('/password/email', [UserController::class, 'sendResetLink'], [GuestMiddleware::class]);
});
