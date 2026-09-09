<?php

function createNotification($fromUserId, $toUserId, $msg, $postId = 0)
{
    global $db;
    $fromUserId = (int) $fromUserId;
    $toUserId = (int) $toUserId;
    $postId = (int) $postId;
    $msg = (string) $msg;

    $stmt = $db->prepare("INSERT INTO notifications (from_user_id, to_user_id, message, post_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('iisi', $fromUserId, $toUserId, $msg, $postId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function getNotifications()
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $stmt = $db->prepare("SELECT * FROM notifications WHERE to_user_id = ? ORDER BY id DESC");
    $stmt->bind_param('i', $currentUserId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function getUnreadNotificationsCount()
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $stmt = $db->prepare("SELECT COUNT(*) AS row FROM notifications WHERE to_user_id = ? AND read_status = 0");
    $stmt->bind_param('i', $currentUserId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['row'] ?? 0);
}

function setNotificationStatusAsRead()
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $stmt = $db->prepare("UPDATE notifications SET read_status = 1 WHERE to_user_id = ?");
    $stmt->bind_param('i', $currentUserId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}
