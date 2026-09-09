<?php

requirePostRequest();
requireCsrf();
requireAdminAuth();
$api = (string) ($_GET['api'] ?? '');

if ($api === 'verify_user') {
    $user = getUser($_POST['user_id'] ?? 0);
    $status = $user && (string) $user['role'] === 'User' && (int) $user['ac_status'] === 0
        ? verifyEmail($user['email'])
        : false;
    jsonResponse(['status' => $status], $status ? 200 : 400);
}
if ($api === 'block_user') {
    $status = blockUserByAdmin($_POST['user_id'] ?? 0);
    jsonResponse(['status' => $status], $status ? 200 : 400);
}
if ($api === 'unblock_user') {
    $status = unblockUserByAdmin($_POST['user_id'] ?? 0);
    jsonResponse(['status' => $status], $status ? 200 : 400);
}

jsonResponse(['status' => false, 'message' => 'Invalid action'], 400);
