<?php
return [
    'db' => [
        'host' => 'localhost',
        'user' => 'root',
        'password' => 'root',
        'database' => 'k1_framework',
        'port' => null,
        'debug' => true,
        'log' => false
    ],
    'io' => [
        'dir_chmod' => 0755,
        'file_chmod' => 0644,
    ],
    'cache' => [
        'type' => 'file',
    ],
    'memcache' => [
        'host' => 'localhost',
        'port' => 11211
    ],
    'cookie' => [
        'domain' => '.' . $_SERVER['HTTP_HOST'],
        'expires' => time() + 31536000,
        'path' => '/',
        'secure' => false,
        'same_site' => 'lax',
        'validation' => true,
        'validation_key' => ',8[UY-yf',
        'hash_method' => 'sha256',
    ],
    'error_handler' => [
        'debug' => true,
        'error_reporting' => E_ALL,
        'log' => false
    ]
];

