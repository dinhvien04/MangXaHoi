<?php

function addComment($postId, $comment)
{
    global $db;
    $currentUserId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $postId = (int) $postId;
    $comment = trim((string) $comment);
    if ($postId <= 0 || $comment === '' || mb_strlen($comment) > 2000 || !canInteractWithPost($postId)) {
        return false;
    }

    $stmt = $db->prepare('INSERT INTO comments (user_id, post_id, comment) VALUES (?, ?, ?)');
    $stmt->bind_param('iis', $currentUserId, $postId, $comment);
    $ok = $stmt->execute();
    $commentId = (int) $db->insert_id;
    $stmt->close();

    if ($ok) {
        $posterId = getPosterId($postId);
        if ($posterId && $posterId !== $currentUserId) {
            createNotification($currentUserId, $posterId, 'đã bình luận về bài đăng của bạn', $postId);
        }
    }
    return $ok ? $commentId : false;
}

function getComments($postId, $limit = 50, $offset = 0)
{
    global $db;
    $postId = (int) $postId;
    $limit = max(1, min(200, (int) $limit));
    $offset = max(0, (int) $offset);
    $stmt = $db->prepare("SELECT c.*, u.username, u.first_name, u.last_name, u.profile_pic
        FROM comments c JOIN users u ON u.id = c.user_id
        WHERE c.post_id = ? AND u.ac_status = 1
        ORDER BY c.id DESC LIMIT ? OFFSET ?");
    $stmt->bind_param('iii', $postId, $limit, $offset);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function getComment($commentId)
{
    global $db;
    $commentId = (int) $commentId;
    if ($commentId <= 0) {
        return null;
    }
    $stmt = $db->prepare('SELECT * FROM comments WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $commentId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function updateOwnComment($commentId, $commentText)
{
    global $db;
    $currentUserId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $commentId = (int) $commentId;
    $commentText = trim((string) $commentText);
    if ($commentId <= 0 || $commentText === '' || mb_strlen($commentText) > 2000) {
        return false;
    }

    $stmt = $db->prepare('UPDATE comments SET comment = ? WHERE id = ? AND user_id = ?');
    $stmt->bind_param('sii', $commentText, $commentId, $currentUserId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    if ($affected > 0) {
        return true;
    }
    $comment = getComment($commentId);
    return $comment && (int) $comment['user_id'] === $currentUserId && (string) $comment['comment'] === $commentText;
}

function deleteOwnComment($commentId)
{
    global $db;
    $currentUserId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $commentId = (int) $commentId;
    if ($commentId <= 0) {
        return false;
    }

    $stmt = $db->prepare('DELETE FROM comments WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $commentId, $currentUserId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected > 0;
}
