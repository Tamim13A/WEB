<?php
/**
 * Application Configuration
 * CIS324 Real Estate Portal
 */

return [
    'app_name' => 'Real Estate Portal',
    'app_version' => '1.0.0',
    'base_url' => $_ENV['APP_BASE_URL'] ?? 'http://localhost',
    'timezone' => 'Asia/Dhaka',
    'locale' => 'en',
    'debug' => (bool)($_ENV['APP_DEBUG'] ?? true),
    'session' => [
        'name' => 'realestate_session',
        'lifetime' => 1800, // 30 minutes
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Strict',
    ],
    'cookie' => [
        'remember_token' => 'realestate_remember',
        'remember_lifetime' => 2592000, // 30 days
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Strict',
    ],
    'csrf' => [
        'token_name' => '_csrf_token',
        'header_name' => 'X-CSRF-Token',
        'lifetime' => 3600, // 1 hour
    ],
    'upload' => [
        'path' => __DIR__ . '/../../public/assets/images/uploads',
        'url_path' => '/assets/images/uploads',
        'max_size' => 5 * 1024 * 1024, // 5MB
        'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp'],
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp'],
    ],
    'pagination' => [
        'default_per_page' => 12,
        'max_per_page' => 50,
    ],
    'roles' => ['admin', 'agent', 'customer'],
];