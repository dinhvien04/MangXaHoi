<?php

$auth = validateAdminSession();
if (!$auth['ok']) {
    if (($auth['reason'] ?? '') === 'unauthenticated') {
        $_SESSION['error'] = [
            'field' => 'checkuser',
            'msg' => 'Hãy đăng nhập bằng tài khoản được cấp quyền Admin để mở Control Center.',
        ];
        header('Location: ../?login&next=admin');
        exit();
    }

    if (!empty($_SESSION['Auth']) && !empty($_SESSION['userdata'])) {
        header('Location: ../?admin_denied=1');
        exit();
    }

    header('Location: ../?login');
    exit();
}

$admin = $auth['admin'];
require __DIR__ . '/dashboard.php';
unset($_SESSION['error']);
