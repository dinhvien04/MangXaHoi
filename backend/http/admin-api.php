<?php

requireAdminAuth();
$api = (string) ($_GET['api'] ?? '');

if ($api === 'verify_user') {
    $user = getUser($_POST['user_id'] ?? 0);
    jsonResponse(['status' => $user ? verifyEmail($user['email']) : false]);
}
if ($api === 'block_user') {
    jsonResponse(['status' => blockUserByAdmin($_POST['user_id'] ?? 0)]);
}
if ($api === 'unblock_user') {
    jsonResponse(['status' => unblockUserByAdmin($_POST['user_id'] ?? 0)]);
}

jsonResponse(['status' => false, 'message' => 'Invalid action'], 400);
