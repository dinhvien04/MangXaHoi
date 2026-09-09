<?php

require_once dirname(__DIR__) . '/backend/bootstrap.php';

if (isset($_GET['action'])) {
    require dirname(__DIR__) . '/backend/http/admin-actions.php';
    exit();
}

if (isset($_GET['api'])) {
    require dirname(__DIR__) . '/backend/http/admin-api.php';
    exit();
}

if (empty($_SESSION['admin_auth'])) {
    require dirname(__DIR__) . '/frontend/admin/login.php';
    unset($_SESSION['error']);
    exit();
}

$admin = getAdmin($_SESSION['admin_auth']);
if (!$admin) {
    unset($_SESSION['admin_auth']);
    header('Location:./');
    exit();
}

require dirname(__DIR__) . '/frontend/admin/dashboard.php';
unset($_SESSION['error']);
