<?php

requirePostRequest();
requireCsrf();
$user = requireUserAuth(false);
$api = (string) ($_GET['api'] ?? '');

if ($api === 'send_message') {
    $status = sendMessage($_POST['user_id'] ?? 0, $_POST['msg'] ?? '');
    jsonResponse(['status' => (bool) $status], $status ? 200 : 400);
}

if ($api === 'get_messages') {
    $conversations = [];
    foreach (getConversationSummaries(30) as $row) {
        $conversations[] = [
            'user' => [
                'id' => (int) $row['user_id'],
                'first_name' => (string) $row['first_name'],
                'last_name' => (string) $row['last_name'],
                'username' => (string) $row['username'],
                'profile_pic' => (string) $row['profile_pic'],
            ],
            'latest_message' => [
                'id' => (int) $row['id'],
                'from_user_id' => (int) $row['from_user_id'],
                'to_user_id' => (int) $row['to_user_id'],
                'msg' => (string) $row['msg'],
                'read_status' => (int) $row['read_status'],
                'created_at' => (string) $row['created_at'],
            ],
        ];
    }

    $response = [
        'status' => true,
        'conversations' => $conversations,
        'new_message_count' => newMsgCount(),
        'blocked' => false,
        'chat' => ['messages' => [], 'user' => null, 'has_more' => false],
    ];

    $chatterId = (int) ($_POST['chatter_id'] ?? 0);
    if ($chatterId > 0) {
        $chatUser = getActiveUser($chatterId);
        if (!$chatUser) {
            jsonResponse(['status' => false, 'message' => 'Người dùng không tồn tại hoặc không hoạt động.'], 404);
        }
        $response['blocked'] = (bool) checkBS($chatterId);
        updateMessageReadStatus($chatterId);
        $beforeId = (int) ($_POST['before_id'] ?? 0);
        $messages = getMessages($chatterId, 51, $beforeId);
        $response['chat']['has_more'] = count($messages) > 50;
        if ($response['chat']['has_more']) {
            array_pop($messages);
        }
        $response['chat']['messages'] = array_map(static function ($message) {
            return [
                'id' => (int) $message['id'],
                'from_user_id' => (int) $message['from_user_id'],
                'to_user_id' => (int) $message['to_user_id'],
                'msg' => (string) $message['msg'],
                'read_status' => (int) $message['read_status'],
                'created_at' => (string) $message['created_at'],
            ];
        }, $messages);
        $response['chat']['user'] = publicUserData($chatUser);
        $response['new_message_count'] = newMsgCount();
    }
    jsonResponse($response);
}

if ($api === 'unblock') {
    $status = unblockUser($_POST['user_id'] ?? 0);
    jsonResponse(['status' => $status], $status ? 200 : 400);
}
if ($api === 'notifications_read') {
    jsonResponse(['status' => setNotificationStatusAsRead()]);
}
if ($api === 'follow') {
    $status = followUser($_POST['user_id'] ?? 0);
    jsonResponse(['status' => $status], $status ? 200 : 400);
}
if ($api === 'unfollow') {
    $status = unfollowUser($_POST['user_id'] ?? 0);
    jsonResponse(['status' => $status], $status ? 200 : 400);
}

if ($api === 'like') {
    $postId = (int) ($_POST['post_id'] ?? 0);
    $status = like($postId);
    jsonResponse(['status' => $status], $status ? 200 : 400);
}
if ($api === 'unlike') {
    $postId = (int) ($_POST['post_id'] ?? 0);
    $status = unlike($postId);
    jsonResponse(['status' => $status], $status ? 200 : 400);
}
if ($api === 'add_comment') {
    $postId = (int) ($_POST['post_id'] ?? 0);
    $comment = trim((string) ($_POST['comment'] ?? ''));
    $commentId = addComment($postId, $comment);
    if (!$commentId) {
        jsonResponse(['status' => false, 'message' => 'Không thể thêm bình luận.'], 400);
    }
    $currentUser = getUser($user['id']);
    $savedComment = getComment($commentId);
    jsonResponse([
        'status' => true,
        'comment' => [
            'id' => (int) $commentId,
            'post_id' => $postId,
            'comment' => (string) ($savedComment['comment'] ?? $comment),
            'created_at' => (string) ($savedComment['created_at'] ?? ''),
            'user' => publicUserData($currentUser),
        ],
    ]);
}
if ($api === 'search') {
    $data = searchUser($_POST['keyword'] ?? '', 10);
    jsonResponse([
        'status' => true,
        'users' => array_map('publicUserData', $data),
    ]);
}

jsonResponse(['status' => false, 'message' => 'Invalid action'], 400);
