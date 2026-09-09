<?php

require_once __DIR__ . '/backend/bootstrap.php';

if (isset($_GET['admin'])) {
    if (isset($_GET['action'])) {
        require __DIR__ . '/backend/http/admin-actions.php';
        exit();
    }

    if (isset($_GET['api'])) {
        require __DIR__ . '/backend/http/admin-api.php';
        exit();
    }

    require __DIR__ . '/frontend/admin/router.php';
    exit();
}

if (isset($_GET['action'])) {
    require __DIR__ . '/backend/http/user-actions.php';
    exit();
}

if (isset($_GET['api'])) {
    require __DIR__ . '/backend/http/api.php';
    exit();
}

require __DIR__ . '/frontend/router.php';
