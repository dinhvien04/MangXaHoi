<?php
require_once 'admin_functions.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['admin_auth'])) {
    http_response_code(403);
    echo json_encode(['status' => false, 'message' => 'Unauthorized']);
    exit();
}

$response = ['status' => false];
$userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;

if ($userId <= 0) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Invalid user ID']);
    exit();
}

if (isset($_GET['verify_user'])) {
    $user = getUser($userId);
    $response['status'] = $user ? verifyEmail($user['email']) : false;
} elseif (isset($_GET['block_user'])) {
    $response['status'] = blockUserByAdmin($userId);
} elseif (isset($_GET['unblock_user'])) {
    $response['status'] = unblockUserByAdmin($userId);
} else {
    http_response_code(400);
    $response['message'] = 'Invalid action';
}

echo json_encode($response);
?>
