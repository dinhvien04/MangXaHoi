<?php

function requireUserAuth()
{
    if (empty($_SESSION['Auth']) || empty($_SESSION['userdata']['id'])) {
        http_response_code(403);
        exit('Bạn cần đăng nhập để thực hiện thao tác này.');
    }
}

function requireAdminAuth()
{
    if (empty($_SESSION['admin_auth'])) {
        http_response_code(403);
        exit('Bạn không có quyền thực hiện thao tác này.');
    }
}

function redirectBackOrHome($fallback = '../../')
{
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if ($referer !== '') {
        $parts = parse_url($referer);
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (empty($parts['host']) || strcasecmp($parts['host'], $host) === 0) {
            header('Location:' . $referer);
            exit();
        }
    }

    header('Location:' . $fallback);
    exit();
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function jsonResponse(array $payload, $status = 200)
{
    http_response_code((int) $status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit();
}
