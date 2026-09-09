<?php

if (empty($_SESSION['admin_auth'])) {
    require __DIR__ . '/login.php';
    unset($_SESSION['error']);
    return;
}

$admin = getAdmin($_SESSION['admin_auth']);
if (!$admin) {
    unset($_SESSION['admin_auth']);
    header('Location:./');
    exit();
}

require __DIR__ . '/dashboard.php';
unset($_SESSION['error']);
