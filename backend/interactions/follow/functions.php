<?php

function checkFollowStatus($userId)
{
    global $db;
    $currentUserId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $userId = (int) $userId;
    if ($currentUserId <= 0 || $userId <= 0) {
        return 0;
    }
    $stmt = $db->prepare('SELECT COUNT(*) AS `row` FROM follow_list WHERE follower_id = ? AND user_id = ?');
    $stmt->bind_param('ii', $currentUserId, $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['row'] ?? 0);
}

function getFollowSuggestions($limit = 20)
{
    global $db;
    $currentUserId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $limit = max(1, min(50, (int) $limit));
    $stmt = $db->prepare("SELECT u.* FROM users u
        WHERE u.id != ? AND u.ac_status = 1
          AND NOT EXISTS (SELECT 1 FROM follow_list f WHERE f.follower_id = ? AND f.user_id = u.id)
          AND NOT EXISTS (SELECT 1 FROM block_list b WHERE (b.user_id = ? AND b.blocked_user_id = u.id) OR (b.user_id = u.id AND b.blocked_user_id = ?))
        ORDER BY u.id DESC LIMIT ?");
    $stmt->bind_param('iiiii', $currentUserId, $currentUserId, $currentUserId, $currentUserId, $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function filterFollowSuggestion()
{
    return array_slice(getFollowSuggestions(5), 0, 5);
}

function getFollowers($userId, $limit = 100)
{
    global $db;
    $userId = (int) $userId;
    $limit = max(1, min(200, (int) $limit));
    $stmt = $db->prepare('SELECT * FROM follow_list WHERE user_id = ? ORDER BY id DESC LIMIT ?');
    $stmt->bind_param('ii', $userId, $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function getFollowing($userId, $limit = 100)
{
    global $db;
    $userId = (int) $userId;
    $limit = max(1, min(200, (int) $limit));
    $stmt = $db->prepare('SELECT * FROM follow_list WHERE follower_id = ? ORDER BY id DESC LIMIT ?');
    $stmt->bind_param('ii', $userId, $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function followUser($userId)
{
    global $db;
    $currentUserId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $userId = (int) $userId;
    if ($userId <= 0 || $userId === $currentUserId || !getActiveUser($userId) || checkBS($userId) || checkFollowStatus($userId)) {
        return false;
    }

    try {
        $stmt = $db->prepare('INSERT INTO follow_list (follower_id, user_id) VALUES (?, ?)');
        $stmt->bind_param('ii', $currentUserId, $userId);
        $ok = $stmt->execute();
        $stmt->close();
    } catch (Throwable $e) {
        return false;
    }

    if ($ok) {
        createNotification($currentUserId, $userId, 'đã bắt đầu theo dõi bạn!');
    }
    return $ok;
}

function unfollowUser($userId)
{
    global $db;
    $currentUserId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $userId = (int) $userId;
    if ($currentUserId <= 0 || $userId <= 0) {
        return false;
    }
    $stmt = $db->prepare('DELETE FROM follow_list WHERE follower_id = ? AND user_id = ?');
    $stmt->bind_param('ii', $currentUserId, $userId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected > 0;
}
