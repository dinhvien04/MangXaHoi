<?php

function checkLikeStatus($postId)
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $postId = (int) $postId;
    $stmt = $db->prepare("SELECT COUNT(*) AS row FROM likes WHERE user_id = ? AND post_id = ?");
    $stmt->bind_param('ii', $currentUserId, $postId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['row'] ?? 0);
}

function getLikes($postId)
{
    global $db;
    $postId = (int) $postId;
    $stmt = $db->prepare("SELECT * FROM likes WHERE post_id = ?");
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function like($postId)
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $postId = (int) $postId;
    if ($postId <= 0 || checkLikeStatus($postId)) {
        return false;
    }

    $stmt = $db->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
    $stmt->bind_param('ii', $postId, $currentUserId);
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok) {
        $posterId = getPosterId($postId);
        if ($posterId && $posterId !== $currentUserId) {
            createNotification($currentUserId, $posterId, 'thích bài viết của bạn!', $postId);
        }
    }
    return $ok;
}

function unlike($postId)
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $postId = (int) $postId;
    $stmt = $db->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
    $stmt->bind_param('ii', $currentUserId, $postId);
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok) {
        $posterId = getPosterId($postId);
        if ($posterId && $posterId !== $currentUserId) {
            createNotification($currentUserId, $posterId, 'đã bỏ thích bài đăng của bạn!', $postId);
        }
    }
    return $ok;
}
