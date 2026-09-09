<?php

function checkBlockStatus($currentUser, $userId)
{
    global $db;
    $currentUser = (int) $currentUser;
    $userId = (int) $userId;
    if ($currentUser <= 0 || $userId <= 0) {
        return 0;
    }
    $stmt = $db->prepare('SELECT COUNT(*) AS `row` FROM block_list WHERE user_id = ? AND blocked_user_id = ?');
    $stmt->bind_param('ii', $currentUser, $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['row'] ?? 0);
}

function checkBS($userId)
{
    global $db;
    $currentUserId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $userId = (int) $userId;
    if ($currentUserId <= 0 || $userId <= 0) {
        return 0;
    }
    $stmt = $db->prepare('SELECT COUNT(*) AS `row` FROM block_list WHERE (user_id = ? AND blocked_user_id = ?) OR (user_id = ? AND blocked_user_id = ?)');
    $stmt->bind_param('iiii', $currentUserId, $userId, $userId, $currentUserId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['row'] ?? 0);
}

function blockUser($blockedUserId)
{
    global $db;
    $currentUserId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $blockedUserId = (int) $blockedUserId;
    if ($blockedUserId <= 0 || $blockedUserId === $currentUserId || !getActiveUser($blockedUserId) || checkBlockStatus($currentUserId, $blockedUserId)) {
        return false;
    }

    $db->begin_transaction();
    try {
        $stmt = $db->prepare('INSERT INTO block_list (user_id, blocked_user_id) VALUES (?, ?)');
        $stmt->bind_param('ii', $currentUserId, $blockedUserId);
        $stmt->execute();
        $stmt->close();

        $stmt = $db->prepare('DELETE FROM follow_list WHERE (follower_id = ? AND user_id = ?) OR (follower_id = ? AND user_id = ?)');
        $stmt->bind_param('iiii', $currentUserId, $blockedUserId, $blockedUserId, $currentUserId);
        $stmt->execute();
        $stmt->close();

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
    $currentUserId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $userId = (int) $userId;
    if ($currentUserId <= 0 || $userId <= 0) {
        return false;
    }
    $stmt = $db->prepare('DELETE FROM block_list WHERE user_id = ? AND blocked_user_id = ?');
    $stmt->bind_param('ii', $currentUserId, $userId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected > 0;
}
