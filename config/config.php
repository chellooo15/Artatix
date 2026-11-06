<?php

return [
    'app' => [
        'name' => 'Artatix',
        'url' => 'http://localhost:5002',
        // 'url' => 'https://artatix.sigmaboyy.my.id',
        'debug' => true,
        'timezone' => 'Asia/Jakarta',
    ],
    // 'db' => [
    //     'host' => '127.0.0.1',
    //     'port' => 3306,
    //     'name' => 'artatix',
    //     'user' => 'artatix_user',
    //     'pass' => 'Artatix123!',
    //     'charset' => 'utf8mb4',
    // ],
    'db' => [
        'host' => '103.119.237.131',
        'port' => 3306,
        'name' => 'cpdicx55109_artatix',
        'user' => 'cpdicx55109_artatix',
        'pass' => 'chello@1290',
        'charset' => 'utf8mb4',
    ],
    'session' => [
        'name' => 'artatix_session',
        'lifetime' => 7200,
        'secure' => false,
        'httponly' => true,
    ],
    'midtrans' => [
        'enabled' => false,
        'server_key' => '',
        'client_key' => '',
        'is_production' => false,
    ],
    'upload' => [
        'path' => __DIR__ . '/../assets/uploads',
        'max_size' => 5 * 1024 * 1024,
        'allowed_types' => ['image/jpeg', 'image/png', 'image/webp'],
    ],
];