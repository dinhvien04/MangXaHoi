<?php

function checkFollowStatus($userId)
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $userId = (int) $userId;
    $stmt = $db->prepare("SELECT COUNT(*) AS row FROM follow_list WHERE follower_id = ? AND user_id = ?");
    $stmt->bind_param('ii', $currentUserId, $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['row'] ?? 0);
}

function getFollowSuggestions()
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $stmt = $db->prepare("SELECT * FROM users WHERE id != ? AND role = 'User'");
    $stmt->bind_param('i', $currentUserId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function filterFollowSuggestion()
{
    $filter = [];
    foreach (getFollowSuggestions() as $user) {
        if (!checkFollowStatus($user['id']) && !checkBS($user['id']) && count($filter) < 5) {
            $filter[] = $user;
        }
    }
    return $filter;
}

function getFollowers($userId)
{
    global $db;
    $userId = (int) $userId;
    $stmt = $db->prepare("SELECT * FROM follow_list WHERE user_id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function getFollowing($userId)
{
    global $db;
    $userId = (int) $userId;
    $stmt = $db->prepare("SELECT * FROM follow_list WHERE follower_id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function followUser($userId)
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $userId = (int) $userId;
    if ($userId <= 0 || $userId === $currentUserId || checkBS($userId) || checkFollowStatus($userId)) {
        return false;
    }

    $stmt = $db->prepare("INSERT INTO follow_list (follower_id, user_id) VALUES (?, ?)");
    $stmt->bind_param('ii', $currentUserId, $userId);
    $ok = $stmt->execute();
    $stmt->close();
    if ($ok) {
        createNotification($currentUserId, $userId, 'đã bắt đầu theo dõi bạn!');
    }
    return $ok;
}

function unfollowUser($userId)
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $userId = (int) $userId;
    $stmt = $db->prepare("DELETE FROM follow_list WHERE follower_id = ? AND user_id = ?");
    $stmt->bind_param('ii', $currentUserId, $userId);
    $ok = $stmt->execute();
    $stmt->close();
    if ($ok) {
        createNotification($currentUserId, $userId, 'đã bỏ theo dõi bạn!');
    }
    return $ok;
}
