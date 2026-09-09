<?php

require_once dirname(__DIR__) . '/app/bootstrap.php';

if (empty($_SESSION['Auth']) || empty($_SESSION['userdata']['id'])) {
    jsonResponse(['status' => false, 'message' => 'Unauthorized'], 403);
}

if (isset($_GET['sendmessage'])) {
    jsonResponse(['status' => sendMessage($_POST['user_id'] ?? 0, $_POST['msg'] ?? '')]);
}

if (isset($_GET['getmessages'])) {
    $chats = getAllMessages();
    $chatlist = '';

    foreach ($chats as $chat) {
        if (empty($chat['messages'])) {
            continue;
        }
        $chatUser = getUser($chat['user_id']);
        if (!$chatUser) {
            continue;
        }

        $latest = $chat['messages'][0];
        $seen = (int) $latest['read_status'] === 1 || (int) $latest['from_user_id'] === (int) $_SESSION['userdata']['id'];
        $chatlist .= '<div class="d-flex justify-content-between border-bottom chatlist_item" data-bs-toggle="modal" data-bs-target="#chatbox" onclick="popchat(' . (int) $chat['user_id'] . ')">
            <div class="d-flex align-items-center p-2">
                <div><img src="assets/images/profile/' . e($chatUser['profile_pic']) . '" alt="" height="40" width="40" class="rounded-circle border"></div>
                <div>&nbsp;&nbsp;</div>
                <div class="d-flex flex-column justify-content-center">
                    <a href="#" class="text-decoration-none text-dark"><h6 style="margin:0;font-size:small;">' . e($chatUser['first_name'] . ' ' . $chatUser['last_name']) . '</h6></a>
                    <p style="margin:0;font-size:small">' . e($latest['msg']) . '</p>
                    <time style="font-size:small" class="timeago text-small" datetime="' . e($latest['created_at']) . '">' . e(gettime($latest['created_at'])) . '</time>
                </div>
            </div>
            <div class="d-flex align-items-center"><div class="p-1 bg-primary rounded-circle ' . ($seen ? 'd-none' : '') . '"></div></div>
        </div>';
    }

    $json = [
        'chatlist' => $chatlist,
        'newmsgcount' => newMsgCount(),
        'blocked' => false,
        'chat' => ['msgs' => '', 'userdata' => null],
    ];

    $chatterId = isset($_POST['chatter_id']) ? (int) $_POST['chatter_id'] : 0;
    if ($chatterId > 0) {
        $messages = getMessages($chatterId);
        $json['blocked'] = (bool) checkBS($chatterId);
        updateMessageReadStatus($chatterId);

        $chatmsg = '';
        foreach ($messages as $message) {
            $mine = (int) $message['from_user_id'] === (int) $_SESSION['userdata']['id'];
            $class1 = $mine ? 'align-self-end bg-primary text-light' : '';
            $class2 = $mine ? 'text-light' : 'text-muted';
            $chatmsg .= '<div class="py-2 px-3 border rounded shadow-sm col-8 d-inline-block ' . $class1 . '">' . e($message['msg']) . '<br>
                <span style="font-size:small" class="' . $class2 . '">' . e(gettime($message['created_at'])) . '</span>
            </div>';
        }
        $json['chat']['msgs'] = $chatmsg;
        $json['chat']['userdata'] = getUser($chatterId);
    } else {
        $json['chat']['msgs'] = '<div class="spinner-border text-center" role="status"></div>';
    }

    jsonResponse($json);
}

if (isset($_GET['unblock'])) {
    jsonResponse(['status' => unblockUser($_POST['user_id'] ?? 0)]);
}

if (isset($_GET['notread'])) {
    jsonResponse(['status' => setNotificationStatusAsRead()]);
}

if (isset($_GET['follow'])) {
    jsonResponse(['status' => followUser($_POST['user_id'] ?? 0)]);
}

if (isset($_GET['unfollow'])) {
    jsonResponse(['status' => unfollowUser($_POST['user_id'] ?? 0)]);
}

if (isset($_GET['like'])) {
    $postId = (int) ($_POST['post_id'] ?? 0);
    jsonResponse(['status' => $postId > 0 && !checkLikeStatus($postId) ? like($postId) : false]);
}

if (isset($_GET['unlike'])) {
    $postId = (int) ($_POST['post_id'] ?? 0);
    jsonResponse(['status' => $postId > 0 && checkLikeStatus($postId) ? unlike($postId) : false]);
}

if (isset($_GET['addcomment'])) {
    $postId = (int) ($_POST['post_id'] ?? 0);
    $comment = trim((string) ($_POST['comment'] ?? ''));
    $response = ['status' => false];

    if ($postId > 0 && $comment !== '' && addComment($postId, $comment)) {
        $currentUser = getUser($_SESSION['userdata']['id']);
        $response['status'] = true;
        $response['comment'] = '<div class="d-flex align-items-center p-2">
            <div><img src="assets/images/profile/' . e($currentUser['profile_pic']) . '" alt="" height="40" class="rounded-circle border"></div>
            <div>&nbsp;&nbsp;&nbsp;</div>
            <div class="d-flex flex-column justify-content-start align-items-start">
                <h6 style="margin:0;"><a href="?u=' . rawurlencode($currentUser['username']) . '" class="text-decoration-none text-muted">@' . e($currentUser['username']) . '</a> - ' . e($comment) . '</h6>
                <p style="margin:0;" class="text-muted">(vừa xong)</p>
            </div>
        </div>';
    }

    jsonResponse($response);
}

if (isset($_GET['search'])) {
    $data = searchUser($_POST['keyword'] ?? '');
    if (!$data) {
        jsonResponse(['status' => false]);
    }

    $users = '';
    foreach ($data as $user) {
        $users .= '<div class="d-flex justify-content-between">
            <div class="d-flex align-items-center p-2">
                <div><img src="assets/images/profile/' . e($user['profile_pic']) . '" alt="" height="40" class="rounded-circle border"></div>
                <div>&nbsp;&nbsp;</div>
                <div class="d-flex flex-column justify-content-center">
                    <a href="?u=' . rawurlencode($user['username']) . '" class="text-decoration-none text-dark"><h6 style="margin:0;font-size:small;">' . e($user['first_name'] . ' ' . $user['last_name']) . '</h6></a>
                    <p style="margin:0;font-size:small" class="text-muted">@' . e($user['username']) . '</p>
                </div>
            </div>
        </div>';
    }

    jsonResponse(['status' => true, 'users' => $users]);
}

jsonResponse(['status' => false, 'message' => 'Invalid action'], 400);
