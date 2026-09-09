<?php

function getConversationSummaries($limit = 30)
{
    global $db;
    $currentUserId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $limit = max(1, min(100, (int) $limit));
    if ($currentUserId <= 0) {
        return [];
    }

    $query = "SELECT m.id, m.from_user_id, m.to_user_id, m.msg, m.read_status, m.created_at,
        u.id AS user_id, u.first_name, u.last_name, u.username, u.profile_pic
        FROM messages m
        JOIN (
            SELECT MAX(id) AS max_id
            FROM messages
            WHERE from_user_id = ? OR to_user_id = ?
            GROUP BY LEAST(from_user_id, to_user_id), GREATEST(from_user_id, to_user_id)
        ) latest ON latest.max_id = m.id
        JOIN users u ON u.id = CASE WHEN m.from_user_id = ? THEN m.to_user_id ELSE m.from_user_id END
        WHERE u.role = 'User' AND u.ac_status = 1
        ORDER BY m.id DESC
        LIMIT ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param('iiii', $currentUserId, $currentUserId, $currentUserId, $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function getActiveChatUserIds($limit = 100)
{
    $ids = [];
    foreach (getConversationSummaries($limit) as $row) {
        $ids[] = (int) $row['user_id'];
    }
    return $ids;
}

function getMessages($userId, $limit = 50, $beforeId = 0)
{
    global $db;
    $currentUserId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $userId = (int) $userId;
    $limit = max(1, min(100, (int) $limit));
    $beforeId = max(0, (int) $beforeId);
    if ($currentUserId <= 0 || $userId <= 0) {
        return [];
    }

    if ($beforeId > 0) {
        $stmt = $db->prepare('SELECT * FROM messages WHERE ((to_user_id = ? AND from_user_id = ?) OR (from_user_id = ? AND to_user_id = ?)) AND id < ? ORDER BY id DESC LIMIT ?');
        $stmt->bind_param('iiiiii', $currentUserId, $userId, $currentUserId, $userId, $beforeId, $limit);
    } else {
        $stmt = $db->prepare('SELECT * FROM messages WHERE (to_user_id = ? AND from_user_id = ?) OR (from_user_id = ? AND to_user_id = ?) ORDER BY id DESC LIMIT ?');
        $stmt->bind_param('iiiii', $currentUserId, $userId, $currentUserId, $userId, $limit);
    }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function sendMessage($userId, $msg)
{
    global $db;
    $currentUserId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $userId = (int) $userId;
    $msg = trim((string) $msg);
    if ($msg === '' || mb_strlen($msg) > 2000 || $userId <= 0 || $userId === $currentUserId || !getActiveUser($userId) || checkBS($userId)) {
        return false;
    }

    if (!consumeRateLimit('message', $currentUserId . '|' . clientIp(), 120, 60)) {
        return false;
    }

    $stmt = $db->prepare('INSERT INTO messages (from_user_id, to_user_id, msg) VALUES (?, ?, ?)');
    $stmt->bind_param('iis', $currentUserId, $userId, $msg);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function newMsgCount()
{
    global $db;
    $currentUserId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $stmt = $db->prepare("SELECT COUNT(*) AS `row` FROM messages m JOIN users u ON u.id = m.from_user_id WHERE m.to_user_id = ? AND m.read_status = 0 AND u.ac_status = 1");
    $stmt->bind_param('i', $currentUserId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['row'] ?? 0);
}

function updateMessageReadStatus($userId)
{
    global $db;
    $currentUserId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $userId = (int) $userId;
    if ($currentUserId <= 0 || $userId <= 0) {
        return false;
    }
    $stmt = $db->prepare('UPDATE messages SET read_status = 1 WHERE to_user_id = ? AND from_user_id = ? AND read_status = 0');
    $stmt->bind_param('ii', $currentUserId, $userId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function getAllMessages()
{
    $conversation = [];
    foreach (getConversationSummaries(30) as $index => $row) {
        $conversation[$index] = [
            'user_id' => (int) $row['user_id'],
            'latest' => $row,
        ];
    }
    return $conversation;
}
