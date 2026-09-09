<?php

function getActiveChatUserIds()
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $stmt = $db->prepare("SELECT from_user_id, to_user_id FROM messages WHERE to_user_id = ? OR from_user_id = ? ORDER BY id DESC");
    $stmt->bind_param('ii', $currentUserId, $currentUserId);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $ids = [];
    foreach ($data as $chat) {
        $from = (int) $chat['from_user_id'];
        $to = (int) $chat['to_user_id'];
        if ($from !== $currentUserId && !in_array($from, $ids, true)) {
            $ids[] = $from;
        }
        if ($to !== $currentUserId && !in_array($to, $ids, true)) {
            $ids[] = $to;
        }
    }
    return $ids;
}

function getMessages($userId)
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $userId = (int) $userId;
    $stmt = $db->prepare("SELECT * FROM messages WHERE (to_user_id = ? AND from_user_id = ?) OR (from_user_id = ? AND to_user_id = ?) ORDER BY id DESC");
    $stmt->bind_param('iiii', $currentUserId, $userId, $currentUserId, $userId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function sendMessage($userId, $msg)
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $userId = (int) $userId;
    $msg = trim((string) $msg);
    if ($msg === '' || $userId <= 0 || $userId === $currentUserId || checkBS($userId)) {
        return false;
    }

    $stmt = $db->prepare("INSERT INTO messages (from_user_id, to_user_id, msg) VALUES (?, ?, ?)");
    $stmt->bind_param('iis', $currentUserId, $userId, $msg);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function newMsgCount()
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $stmt = $db->prepare("SELECT COUNT(*) AS row FROM messages WHERE to_user_id = ? AND read_status = 0");
    $stmt->bind_param('i', $currentUserId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['row'] ?? 0);
}

function updateMessageReadStatus($userId)
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $userId = (int) $userId;
    $stmt = $db->prepare("UPDATE messages SET read_status = 1 WHERE to_user_id = ? AND from_user_id = ?");
    $stmt->bind_param('ii', $currentUserId, $userId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function getAllMessages()
{
    $conversation = [];
    foreach (getActiveChatUserIds() as $index => $id) {
        $conversation[$index]['user_id'] = $id;
        $conversation[$index]['messages'] = getMessages($id);
    }
    return $conversation;
}
