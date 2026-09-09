<?php

function addComment($postId, $comment)
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $postId = (int) $postId;
    $comment = trim((string) $comment);
    if ($postId <= 0 || $comment === '') {
        return false;
    }

    $stmt = $db->prepare("INSERT INTO comments (user_id, post_id, comment) VALUES (?, ?, ?)");
    $stmt->bind_param('iis', $currentUserId, $postId, $comment);
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok) {
        $posterId = getPosterId($postId);
        if ($posterId && $posterId !== $currentUserId) {
            createNotification($currentUserId, $posterId, 'đã bình luận về bài đăng của bạn', $postId);
        }
    }
    return $ok;
}

function getComments($postId)
{
    global $db;
    $postId = (int) $postId;
    $stmt = $db->prepare("SELECT * FROM comments WHERE post_id = ? ORDER BY id DESC");
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function updateOwnComment($commentId, $commentText)
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $commentId = (int) $commentId;
    $commentText = trim((string) $commentText);
    if ($commentId <= 0 || $commentText === '') {
        return false;
    }

    $stmt = $db->prepare('UPDATE comments SET comment = ? WHERE id = ? AND user_id = ?');
    $stmt->bind_param('sii', $commentText, $commentId, $currentUserId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected > 0;
}

function deleteOwnComment($commentId)
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
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
