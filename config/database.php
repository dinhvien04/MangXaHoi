<?php

return [
    'name' => getenv('DB_NAME') ?: 'handbook',
    'host' => getenv('DB_HOST') ?: 'localhost',
    'port' => getenv('DB_PORT') !== false && getenv('DB_PORT') !== '' ? (int) getenv('DB_PORT') : null,
    'user' => getenv('DB_USER') ?: 'root',
    'pass' => getenv('DB_PASS') ?: '',
    'charset' => 'utf8mb4',
];
