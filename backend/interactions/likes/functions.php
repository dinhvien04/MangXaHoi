<?php

function checkLikeStatus($postId)
{
    global $db;
    $currentUserId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $postId = (int) $postId;
    if ($currentUserId <= 0 || $postId <= 0) {
        return 0;
    }
    $stmt = $db->prepare('SELECT COUNT(*) AS row FROM likes WHERE user_id = ? AND post_id = ?');
    $stmt->bind_param('ii', $currentUserId, $postId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['row'] ?? 0);
}

function getLikes($postId, $limit = 500)
{
    global $db;
    $postId = (int) $postId;
    $limit = max(1, min(500, (int) $limit));
    $stmt = $db->prepare('SELECT * FROM likes WHERE post_id = ? ORDER BY id DESC LIMIT ?');
    $stmt->bind_param('ii', $postId, $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function like($postId)
{
    global $db;
    $currentUserId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $postId = (int) $postId;
    if ($postId <= 0 || !canInteractWithPost($postId) || checkLikeStatus($postId)) {
        return false;
    }

    try {
        $stmt = $db->prepare('INSERT INTO likes (post_id, user_id) VALUES (?, ?)');
        $stmt->bind_param('ii', $postId, $currentUserId);
        $ok = $stmt->execute();
        $stmt->close();
    } catch (Throwable $e) {
        return false;
    }

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
    $currentUserId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $postId = (int) $postId;
    if ($postId <= 0) {
        return false;
    }
    $stmt = $db->prepare('DELETE FROM likes WHERE user_id = ? AND post_id = ?');
    $stmt->bind_param('ii', $currentUserId, $postId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected > 0;
}
