<?php
/**
 * https://siipo.la/blog/how-to-use-eloquent-orm-migrations-outside-laravel
 */
$settings = require_once __DIR__ . '/../settings.php';
$dbSettings = \suplascripts\app\Application::getInstance()->getSetting('db');

return [
    'paths' => [
        'migrations' => __DIR__ . '/migrations',
        'seeds' => __DIR__ . '/seeds',
    ],
    'migration_base_class' => \suplascripts\database\migrations\Migration::class,
    'environments' => [
        'default_migration_table' => 'migrations',
        'default_database' => 'db',
        'db' => [
            'adapter' => $dbSettings['driver'],
            'host' => $dbSettings['host'] ?? 'localhost',
            'name' => $dbSettings['database'],
            'user' => $dbSettings['username'] ?? '',
            'pass' => $dbSettings['password'] ?? '',
        ]
    ]
];
