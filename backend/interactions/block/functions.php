<?php

function checkBlockStatus($currentUser, $userId)
{
    global $db;
    $currentUser = (int) $currentUser;
    $userId = (int) $userId;
    $stmt = $db->prepare("SELECT COUNT(*) AS row FROM block_list WHERE user_id = ? AND blocked_user_id = ?");
    $stmt->bind_param('ii', $currentUser, $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['row'] ?? 0);
}

function checkBS($userId)
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $userId = (int) $userId;
    $stmt = $db->prepare("SELECT COUNT(*) AS row FROM block_list WHERE (user_id = ? AND blocked_user_id = ?) OR (user_id = ? AND blocked_user_id = ?)");
    $stmt->bind_param('iiii', $currentUserId, $userId, $userId, $currentUserId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['row'] ?? 0);
}

function blockUser($blockedUserId)
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $blockedUserId = (int) $blockedUserId;
    if ($blockedUserId <= 0 || $blockedUserId === $currentUserId || checkBlockStatus($currentUserId, $blockedUserId)) {
        return false;
    }

    $db->begin_transaction();
    try {
        $stmt = $db->prepare("INSERT INTO block_list (user_id, blocked_user_id) VALUES (?, ?)");
        $stmt->bind_param('ii', $currentUserId, $blockedUserId);
        $stmt->execute();
        $stmt->close();

        $stmt = $db->prepare("DELETE FROM follow_list WHERE (follower_id = ? AND user_id = ?) OR (follower_id = ? AND user_id = ?)");
        $stmt->bind_param('iiii', $currentUserId, $blockedUserId, $blockedUserId, $currentUserId);
        $stmt->execute();
        $stmt->close();

        createNotification($currentUserId, $blockedUserId, 'đã chặn bạn');
        $db->commit();
        return true;
    } catch (Throwable $e) {
        $db->rollback();
        return false;
    }
}

function unblockUser($userId)
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $userId = (int) $userId;
    $stmt = $db->prepare("DELETE FROM block_list WHERE user_id = ? AND blocked_user_id = ?");
    $stmt->bind_param('ii', $currentUserId, $userId);
    $ok = $stmt->execute();
    $stmt->close();
    if ($ok) {
        createNotification($currentUserId, $userId, 'đã bỏ chặn bạn!');
    }
    return $ok;
}
