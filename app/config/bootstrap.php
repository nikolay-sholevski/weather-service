<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

if (class_exists(Dotenv::class)) {
    // Това ще зареди .env, .env.local, .env.test и т.н. според APP_ENV
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}
