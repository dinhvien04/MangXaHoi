<?php

requireUserAuth();
$api = (string) ($_GET['api'] ?? '');

if ($api === 'send_message') {
    jsonResponse(['status' => sendMessage($_POST['user_id'] ?? 0, $_POST['msg'] ?? '')]);
}

if ($api === 'get_messages') {
    $chatlist = '';
    foreach (getAllMessages() as $chat) {
        if (empty($chat['messages'])) continue;
        $chatUser = getUser($chat['user_id']);
        if (!$chatUser) continue;
        $latest = $chat['messages'][0];
        $seen = (int) $latest['read_status'] === 1 || (int) $latest['from_user_id'] === (int) $_SESSION['userdata']['id'];
        $chatlist .= '<div class="d-flex justify-content-between border-bottom chatlist_item" data-bs-toggle="modal" data-bs-target="#chatbox" onclick="popchat(' . (int) $chat['user_id'] . ')"><div class="d-flex align-items-center p-2"><div><img src="public/images/profile/' . e($chatUser['profile_pic']) . '" height="40" width="40" class="rounded-circle border"></div><div>&nbsp;&nbsp;</div><div class="d-flex flex-column justify-content-center"><a href="?u=' . rawurlencode($chatUser['username']) . '" class="text-decoration-none text-dark"><h6 style="margin:0;font-size:small;">' . e($chatUser['first_name'] . ' ' . $chatUser['last_name']) . '</h6></a><p style="margin:0;font-size:small">' . e($latest['msg']) . '</p><time style="font-size:small" class="timeago text-small" datetime="' . e($latest['created_at']) . '">' . e(gettime($latest['created_at'])) . '</time></div></div><div class="d-flex align-items-center"><div class="p-1 bg-primary rounded-circle ' . ($seen ? 'd-none' : '') . '"></div></div></div>';
    }

    $json = ['chatlist' => $chatlist, 'newmsgcount' => newMsgCount(), 'blocked' => false, 'chat' => ['msgs' => '', 'userdata' => null]];
    $chatterId = (int) ($_POST['chatter_id'] ?? 0);
    if ($chatterId > 0) {
        $json['blocked'] = (bool) checkBS($chatterId);
        updateMessageReadStatus($chatterId);
        $chatmsg = '';
        foreach (getMessages($chatterId) as $message) {
            $mine = (int) $message['from_user_id'] === (int) $_SESSION['userdata']['id'];
            $chatmsg .= '<div class="py-2 px-3 border rounded shadow-sm col-8 d-inline-block ' . ($mine ? 'align-self-end bg-primary text-light' : '') . '">' . e($message['msg']) . '<br><span style="font-size:small" class="' . ($mine ? 'text-light' : 'text-muted') . '">' . e(gettime($message['created_at'])) . '</span></div>';
        }
        $json['chat']['msgs'] = $chatmsg;
        $json['chat']['userdata'] = publicUserData(getUser($chatterId));
    }
    jsonResponse($json);
}

if ($api === 'unblock') jsonResponse(['status' => unblockUser($_POST['user_id'] ?? 0)]);
if ($api === 'notifications_read') jsonResponse(['status' => setNotificationStatusAsRead()]);
if ($api === 'follow') jsonResponse(['status' => followUser($_POST['user_id'] ?? 0)]);
if ($api === 'unfollow') jsonResponse(['status' => unfollowUser($_POST['user_id'] ?? 0)]);

if ($api === 'like') {
    $postId = (int) ($_POST['post_id'] ?? 0);
    jsonResponse(['status' => $postId > 0 && !checkLikeStatus($postId) ? like($postId) : false]);
}
if ($api === 'unlike') {
    $postId = (int) ($_POST['post_id'] ?? 0);
    jsonResponse(['status' => $postId > 0 && checkLikeStatus($postId) ? unlike($postId) : false]);
}
if ($api === 'add_comment') {
    $postId = (int) ($_POST['post_id'] ?? 0);
    $comment = trim((string) ($_POST['comment'] ?? ''));
    $response = ['status' => false];
    if ($postId > 0 && $comment !== '' && addComment($postId, $comment)) {
        $currentUser = getUser($_SESSION['userdata']['id']);
        $response['status'] = true;
        $response['comment'] = '<div class="d-flex align-items-center p-2"><div><img src="public/images/profile/' . e($currentUser['profile_pic']) . '" height="40" class="rounded-circle border"></div><div>&nbsp;&nbsp;&nbsp;</div><div><h6 style="margin:0;"><a href="?u=' . rawurlencode($currentUser['username']) . '" class="text-decoration-none text-muted">@' . e($currentUser['username']) . '</a> - ' . e($comment) . '</h6><p style="margin:0;" class="text-muted">(vừa xong)</p></div></div>';
    }
    jsonResponse($response);
}
if ($api === 'search') {
    $data = searchUser($_POST['keyword'] ?? '');
    if (!$data) jsonResponse(['status' => false]);
    $users = '';
    foreach ($data as $found) {
        $users .= '<div class="d-flex justify-content-between"><div class="d-flex align-items-center p-2"><img src="public/images/profile/' . e($found['profile_pic']) . '" height="40" class="rounded-circle border"><div class="ms-2"><a href="?u=' . rawurlencode($found['username']) . '" class="text-decoration-none text-dark"><h6 class="mb-0">' . e($found['first_name'] . ' ' . $found['last_name']) . '</h6></a><p class="mb-0 small text-muted">@' . e($found['username']) . '</p></div></div></div>';
    }
    jsonResponse(['status' => true, 'users' => $users]);
}

jsonResponse(['status' => false, 'message' => 'Invalid action'], 400);
