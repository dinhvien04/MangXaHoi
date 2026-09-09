<?php

require_once dirname(__DIR__) . '/app/bootstrap.php';

requireAdminAuth();

if (isset($_GET['verify_user'])) {
    $user = getUser($_POST['user_id'] ?? 0);
    jsonResponse(['status' => $user ? verifyEmail($user['email']) : false]);
}

if (isset($_GET['block_user'])) {
    jsonResponse(['status' => blockUserByAdmin($_POST['user_id'] ?? 0)]);
}

if (isset($_GET['unblock_user'])) {
    jsonResponse(['status' => unblockUserByAdmin($_POST['user_id'] ?? 0)]);
}

jsonResponse(['status' => false, 'message' => 'Invalid action'], 400);
