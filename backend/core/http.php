<?php

function validateUserSession($allowUnverified = false)
{
    $userId = (int) ($_SESSION['userdata']['id'] ?? 0);
    if (empty($_SESSION['Auth']) || $userId <= 0) {
        return ['ok' => false, 'reason' => 'unauthenticated', 'user' => null];
    }

    $user = getUser($userId);
    if (!$user || (string) $user['role'] !== 'User') {
        unset($_SESSION['Auth'], $_SESSION['userdata'], $_SESSION['email_otp']);
        return ['ok' => false, 'reason' => 'invalid_session', 'user' => null];
    }

    $status = (int) $user['ac_status'];
    if ($status === 2) {
        $_SESSION['userdata'] = $user;
        return ['ok' => false, 'reason' => 'blocked', 'user' => $user];
    }
    if (!$allowUnverified && $status !== 1) {
        $_SESSION['userdata'] = $user;
        return ['ok' => false, 'reason' => 'unverified', 'user' => $user];
    }

    $_SESSION['userdata'] = $user;
    return ['ok' => true, 'reason' => null, 'user' => $user];
}

function requireUserAuth($allowUnverified = false)
{
    $auth = validateUserSession($allowUnverified);
    if ($auth['ok']) {
        return $auth['user'];
    }

    http_response_code($auth['reason'] === 'unauthenticated' ? 401 : 403);
    $messages = [
        'unauthenticated' => 'Bạn cần đăng nhập để thực hiện thao tác này.',
        'invalid_session' => 'Phiên đăng nhập không còn hợp lệ.',
        'blocked' => 'Tài khoản của bạn đã bị chặn.',
        'unverified' => 'Bạn cần xác minh email trước khi thực hiện thao tác này.',
    ];
    exit($messages[$auth['reason']] ?? 'Bạn không có quyền thực hiện thao tác này.');
}

function validateAdminSession()
{
    $adminId = (int) ($_SESSION['admin_auth'] ?? 0);
    if ($adminId <= 0) {
        return ['ok' => false, 'admin' => null];
    }

    $admin = getAdmin($adminId);
    if (!$admin) {
        unset($_SESSION['admin_auth']);
        return ['ok' => false, 'admin' => null];
    }

    return ['ok' => true, 'admin' => $admin];
}

function requireAdminAuth()
{
    $auth = validateAdminSession();
    if ($auth['ok']) {
        return $auth['admin'];
    }

    http_response_code(403);
    exit('Bạn không có quyền thực hiện thao tác này.');
}

function redirectBackOrHome($fallback = './')
{
    $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');
    if ($referer !== '') {
        $parts = parse_url($referer);
        $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $safeScheme = $scheme === '' || $scheme === 'http' || $scheme === 'https';
        $safeHost = empty($parts['host']) || strcasecmp((string) $parts['host'], $host) === 0;
        if ($safeScheme && $safeHost) {
            header('Location: ' . $referer);
            exit();
        }
    }

    header('Location: ' . $fallback);
    exit();
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function jsonResponse(array $payload, $status = 200)
{
    http_response_code((int) $status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}
