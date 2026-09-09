<?php

$config = require dirname(__DIR__, 2) . '/config/database.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $db = new mysqli(
        (string) $config['host'],
        (string) $config['user'],
        (string) $config['pass'],
        (string) $config['name']
    );
    $db->set_charset((string) $config['charset']);
} catch (Throwable $e) {
    error_log('Handbook database connection error: ' . $e->getMessage());
    http_response_code(500);
    exit('Không thể kết nối cơ sở dữ liệu. Vui lòng thử lại sau.');
}
