<?php

$config = require dirname(__DIR__, 2) . '/config/database.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = (string) ($config['host'] ?? 'localhost');
$user = (string) ($config['user'] ?? 'root');
$pass = (string) ($config['pass'] ?? '');
$name = (string) ($config['name'] ?? 'handbook');
$charset = (string) ($config['charset'] ?? 'utf8mb4');

$portsToTry = [];
if (!empty($config['port'])) {
    $portsToTry[] = (int) $config['port'];
} else {
    // Current XAMPP MySQL is configured on port 8111; fallback to standard 3306
    $portsToTry = [8111, 3306];
}

$db = null;
$lastException = null;

foreach ($portsToTry as $port) {
    try {
        $db = new mysqli($host, $user, $pass, $name, $port);
        break;
    } catch (Throwable $e) {
        $lastException = $e;
    }
}

if (!$db) {
    error_log('Handbook database connection error: ' . ($lastException ? $lastException->getMessage() : 'Unknown error'));
    http_response_code(500);
    exit('Không thể kết nối cơ sở dữ liệu. Vui lòng thử lại sau.');
}

$db->set_charset($charset);
