<?php

declare(strict_types=1);

// Autoload Composer
require dirname(__DIR__) . '/vendor/autoload.php';

// Ensure APP_ENV=test
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'test';
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = '1';

// Load .env.test (if exists)
if (file_exists(dirname(__DIR__) . '/.env.test')) {
    (new Symfony\Component\Dotenv\Dotenv())->loadEnv(dirname(__DIR__) . '/.env.test');
}

// Nothing else to bootstrap — PHPUnit + SymfonyExtension will handle Kernel

