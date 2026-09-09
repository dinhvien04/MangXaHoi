<?php

function getPostById($userId, $limit = 50, $offset = 0)
{
    global $db;
    $userId = (int) $userId;
    $limit = max(1, min(100, (int) $limit));
    $offset = max(0, (int) $offset);
    $stmt = $db->prepare("SELECT p.* FROM posts p JOIN users u ON u.id = p.user_id WHERE p.user_id = ? AND p.is_approved = 1 AND u.ac_status = 1 ORDER BY p.id DESC LIMIT ? OFFSET ?");
    $stmt->bind_param('iii', $userId, $limit, $offset);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function getPostRecord($postId)
{
    global $db;
    $postId = (int) $postId;
    if ($postId <= 0) {
        return null;
    }
    $stmt = $db->prepare('SELECT * FROM posts WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function getPosterId($postId)
{
    $post = getPostRecord($postId);
    return $post ? (int) $post['user_id'] : 0;
}

function canInteractWithPost($postId)
{
    global $db;
    $currentUserId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $postId = (int) $postId;
    if ($currentUserId <= 0 || $postId <= 0) {
        return false;
    }
    $stmt = $db->prepare("SELECT p.id, p.user_id FROM posts p JOIN users u ON u.id = p.user_id WHERE p.id = ? AND p.is_approved = 1 AND u.ac_status = 1 LIMIT 1");
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $post = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$post) {
        return false;
    }
    return (int) $post['user_id'] === $currentUserId || !checkBS((int) $post['user_id']);
}

function getPost($limit = 100, $offset = 0)
{
    global $db;
    $limit = max(1, min(100, (int) $limit));
    $offset = max(0, (int) $offset);
    $stmt = $db->prepare("SELECT users.id AS uid, posts.id, posts.user_id, posts.post_img, posts.post_text, posts.created_at, users.first_name, users.last_name, users.username, users.profile_pic FROM posts JOIN users ON users.id = posts.user_id WHERE posts.is_approved = 1 AND users.ac_status = 1 ORDER BY posts.id DESC LIMIT ? OFFSET ?");
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function filterPosts($limit = 50, $offset = 0)
{
    global $db;
    $currentUserId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $limit = max(1, min(100, (int) $limit));
    $offset = max(0, (int) $offset);
    if ($currentUserId <= 0) {
        return [];
    }

    $query = "SELECT u.id AS uid, p.id, p.user_id, p.post_img, p.post_text, p.created_at, u.first_name, u.last_name, u.username, u.profile_pic,
        (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
        (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count,
        EXISTS(SELECT 1 FROM likes ml WHERE ml.post_id = p.id AND ml.user_id = ?) AS liked_by_me
        FROM posts p
        JOIN users u ON u.id = p.user_id
        LEFT JOIN follow_list f ON f.user_id = p.user_id AND f.follower_id = ?
        WHERE p.is_approved = 1
          AND u.ac_status = 1
          AND (p.user_id = ? OR f.id IS NOT NULL)
          AND NOT EXISTS (SELECT 1 FROM block_list b WHERE (b.user_id = ? AND b.blocked_user_id = p.user_id) OR (b.user_id = p.user_id AND b.blocked_user_id = ?))
        ORDER BY p.id DESC LIMIT ? OFFSET ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param('iiiiiii', $currentUserId, $currentUserId, $currentUserId, $currentUserId, $currentUserId, $limit, $offset);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function deletePost($postId, $asAdmin = false)
{
    global $db;
    $postId = (int) $postId;
    $post = getPostRecord($postId);
    if (!$post) {
        return false;
    }

    if ($asAdmin) {
        if (!validateAdminSession()['ok']) {
            return false;
        }
    } elseif ((int) $post['user_id'] !== (int) ($_SESSION['userdata']['id'] ?? 0)) {
        return false;
    }

    $db->begin_transaction();
    try {
        foreach (['likes', 'comments', 'notifications'] as $table) {
            $stmt = $db->prepare("DELETE FROM {$table} WHERE post_id = ?");
            $stmt->bind_param('i', $postId);
            $stmt->execute();
            $stmt->close();
        }
        $stmt = $db->prepare('DELETE FROM posts WHERE id = ?');
        $stmt->bind_param('i', $postId);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        if ($affected !== 1) {
            throw new RuntimeException('Post was not deleted');
        }
        $db->commit();
    } catch (Throwable $e) {
        $db->rollback();
        return false;
    }

    if (!empty($post['post_img'])) {
        @unlink(dirname(__DIR__, 2) . '/public/images/posts/' . basename((string) $post['post_img']));
    }
    return true;
}

function validatePostImage($imageData)
{
    if (empty($imageData['name'])) {
        return ['status' => false, 'msg' => 'Vui lòng chọn hình ảnh', 'field' => 'post_img'];
    }
    if (($imageData['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || empty($imageData['tmp_name']) || !is_file($imageData['tmp_name'])) {
        return ['status' => false, 'msg' => 'Ảnh tải lên không hợp lệ', 'field' => 'post_img'];
    }
    if ((int) ($imageData['size'] ?? 0) <= 0 || (int) ($imageData['size'] ?? 0) > 2000000) {
        return ['status' => false, 'msg' => 'Ảnh bài đăng phải nhỏ hơn 2 MB', 'field' => 'post_img'];
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($imageData['tmp_name']);
    if (!in_array($mime, ['image/jpeg', 'image/png'], true) || @getimagesize($imageData['tmp_name']) === false) {
        return ['status' => false, 'msg' => 'Chỉ cho phép hình ảnh JPG hoặc PNG hợp lệ', 'field' => 'post_img'];
    }
    return ['status' => true];
}

function createPost($text, $image)
{
    global $db;
    $postText = trim((string) ($text['post_text'] ?? ''));
    if (mb_strlen($postText) > 10000) {
        return false;
    }
    $userId = (int) ($_SESSION['userdata']['id'] ?? 0);
    if (!getActiveUser($userId)) {
        return false;
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($image['tmp_name']);
    $extension = $mime === 'image/png' ? 'png' : 'jpg';
    $imageName = bin2hex(random_bytes(16)) . '.' . $extension;
    $imageDir = dirname(__DIR__, 2) . '/public/images/posts';
    if (!is_dir($imageDir) && !mkdir($imageDir, 0775, true) && !is_dir($imageDir)) {
        return false;
    }
    $destination = $imageDir . '/' . $imageName;
    if (!move_uploaded_file($image['tmp_name'], $destination)) {
        return false;
    }

    try {
        $stmt = $db->prepare('INSERT INTO posts (user_id, post_text, post_img, is_reported, is_approved) VALUES (?, ?, ?, 0, 1)');
        $stmt->bind_param('iss', $userId, $postText, $imageName);
        $ok = $stmt->execute();
        $stmt->close();
    } catch (Throwable $e) {
        $ok = false;
    }

    if (!$ok) {
        @unlink($destination);
    }
    return $ok;
}

function updateOwnPost($postId, $postContent)
{
    global $db;
    $postId = (int) $postId;
    $userId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $postContent = trim((string) $postContent);
    if ($postId <= 0 || $userId <= 0 || mb_strlen($postContent) > 10000) {
        return false;
    }
    $stmt = $db->prepare('UPDATE posts SET post_text = ? WHERE id = ? AND user_id = ?');
    $stmt->bind_param('sii', $postContent, $postId, $userId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    if ($affected > 0) {
        return true;
    }
    $post = getPostRecord($postId);
    return $post && (int) $post['user_id'] === $userId && (string) $post['post_text'] === $postContent;
}

function reportPost($postId)
{
    global $db;
    $postId = (int) $postId;
    $currentUserId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $post = getPostRecord($postId);
    if (!$post || $currentUserId <= 0 || (int) $post['user_id'] === $currentUserId || !canInteractWithPost($postId)) {
        return false;
    }
    $stmt = $db->prepare('UPDATE posts SET is_reported = 1 WHERE id = ? AND is_reported = 0');
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected > 0;
}
