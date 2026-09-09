<?php

function createNotification($fromUserId, $toUserId, $msg, $postId = 0)
{
    global $db;
    $fromUserId = (int) $fromUserId;
    $toUserId = (int) $toUserId;
    $msg = trim((string) $msg);
    $postId = (int) $postId;

    if ($fromUserId <= 0 || $toUserId <= 0 || $fromUserId === $toUserId || $msg === '' || !getUser($fromUserId) || !getUser($toUserId)) {
        return false;
    }
    if ($postId > 0 && !getPostRecord($postId)) {
        return false;
    }

    if ($postId > 0) {
        $stmt = $db->prepare('INSERT INTO notifications (from_user_id, to_user_id, message, post_id) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('iisi', $fromUserId, $toUserId, $msg, $postId);
    } else {
        $stmt = $db->prepare('INSERT INTO notifications (from_user_id, to_user_id, message, post_id) VALUES (?, ?, ?, NULL)');
        $stmt->bind_param('iis', $fromUserId, $toUserId, $msg);
    }
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function getNotifications($limit = 50, $offset = 0)
{
    global $db;
    $currentUserId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $limit = max(1, min(100, (int) $limit));
    $offset = max(0, (int) $offset);
    $stmt = $db->prepare("SELECT n.*, u.first_name AS from_first_name, u.last_name AS from_last_name, u.username AS from_username, u.profile_pic AS from_profile_pic
        FROM notifications n JOIN users u ON u.id = n.from_user_id
        WHERE n.to_user_id = ? AND u.ac_status = 1
        ORDER BY n.id DESC LIMIT ? OFFSET ?");
    $stmt->bind_param('iii', $currentUserId, $limit, $offset);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function getUnreadNotificationsCount()
{
    global $db;
    $currentUserId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $stmt = $db->prepare("SELECT COUNT(*) AS row FROM notifications n JOIN users u ON u.id = n.from_user_id WHERE n.to_user_id = ? AND n.read_status = 0 AND u.ac_status = 1");
    $stmt->bind_param('i', $currentUserId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['row'] ?? 0);
}

function setNotificationStatusAsRead()
{
    global $db;
    $currentUserId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $stmt = $db->prepare('UPDATE notifications SET read_status = 1 WHERE to_user_id = ? AND read_status = 0');
    $stmt->bind_param('i', $currentUserId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}
