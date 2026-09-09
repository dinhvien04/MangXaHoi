<?php

require_once __DIR__ . '/backend/bootstrap.php';

if (isset($_GET['action'])) {
    require __DIR__ . '/backend/http/user-actions.php';
    exit();
}

if (isset($_GET['api'])) {
    require __DIR__ . '/backend/http/api.php';
    exit();
}

require __DIR__ . '/frontend/router.php';
