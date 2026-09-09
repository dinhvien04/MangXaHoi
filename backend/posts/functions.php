<?php

function getPostById($userId)
{
    global $db;
    $userId = (int) $userId;
    $stmt = $db->prepare('SELECT * FROM posts WHERE user_id = ? ORDER BY id DESC');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function getPosterId($postId)
{
    global $db;
    $postId = (int) $postId;
    $stmt = $db->prepare('SELECT user_id FROM posts WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return isset($row['user_id']) ? (int) $row['user_id'] : 0;
}

function getPost()
{
    global $db;
    $query = 'SELECT users.id AS uid, posts.id, posts.user_id, posts.post_img, posts.post_text, posts.created_at, users.first_name, users.last_name, users.username, users.profile_pic FROM posts JOIN users ON users.id = posts.user_id ORDER BY posts.id DESC';
    $run = mysqli_query($db, $query);
    return $run ? mysqli_fetch_all($run, MYSQLI_ASSOC) : [];
}

function filterPosts()
{
    $filter = [];
    foreach (getPost() as $post) {
        if (checkFollowStatus($post['user_id']) || (int) $post['user_id'] === (int) $_SESSION['userdata']['id']) {
            $filter[] = $post;
        }
    }
    return $filter;
}

function deletePost($postId)
{
    global $db;
    $postId = (int) $postId;
    if ($postId <= 0) {
        return false;
    }

    $isAdmin = !empty($_SESSION['admin_auth']);
    if (!$isAdmin) {
        if (empty($_SESSION['userdata']['id']) || getPosterId($postId) !== (int) $_SESSION['userdata']['id']) {
            return false;
        }
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
        $ok = $stmt->execute();
        $stmt->close();
        $db->commit();
        return $ok;
    } catch (Throwable $e) {
        $db->rollback();
        return false;
    }
}

function validatePostImage($imageData)
{
    if (empty($imageData['name'])) {
        return ['status' => false, 'msg' => 'Vui lòng chọn hình ảnh', 'field' => 'post_img'];
    }
    if (($imageData['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || empty($imageData['tmp_name'])) {
        return ['status' => false, 'msg' => 'Ảnh tải lên không hợp lệ', 'field' => 'post_img'];
    }
    if ((int) ($imageData['size'] ?? 0) > 2000000) {
        return ['status' => false, 'msg' => 'Tải lên hình ảnh có kích thước nhỏ hơn 2 MB', 'field' => 'post_img'];
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($imageData['tmp_name']);
    if (!in_array($mime, ['image/jpeg', 'image/png'], true)) {
        return ['status' => false, 'msg' => 'Chỉ cho phép hình ảnh jpg, jpeg, png', 'field' => 'post_img'];
    }
    return ['status' => true];
}

function createPost($text, $image)
{
    global $db;
    $postText = trim((string) ($text['post_text'] ?? ''));
    $userId = (int) $_SESSION['userdata']['id'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($image['tmp_name']);
    $extension = $mime === 'image/png' ? 'png' : 'jpg';
    $imageName = bin2hex(random_bytes(12)) . '.' . $extension;
    $imageDir = dirname(__DIR__, 2) . '/public/images/posts';
    if (!is_dir($imageDir) && !mkdir($imageDir, 0775, true) && !is_dir($imageDir)) {
        return false;
    }
    if (!move_uploaded_file($image['tmp_name'], $imageDir . '/' . $imageName)) {
        return false;
    }

    $stmt = $db->prepare('INSERT INTO posts (user_id, post_text, post_img) VALUES (?, ?, ?)');
    $stmt->bind_param('iss', $userId, $postText, $imageName);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function updateOwnPost($postId, $postContent)
{
    global $db;
    $postId = (int) $postId;
    $userId = (int) ($_SESSION['userdata']['id'] ?? 0);
    if ($postId <= 0 || $userId <= 0) {
        return false;
    }
    $postContent = trim((string) $postContent);
    $stmt = $db->prepare('UPDATE posts SET post_text = ? WHERE id = ? AND user_id = ?');
    $stmt->bind_param('sii', $postContent, $postId, $userId);
    $stmt->execute();
    $ok = $stmt->affected_rows >= 0 && getPosterId($postId) === $userId;
    $stmt->close();
    return $ok;
}

function reportPost($postId)
{
    global $db;
    $postId = (int) $postId;
    if ($postId <= 0) {
        return false;
    }
    $stmt = $db->prepare('UPDATE posts SET is_reported = 1, is_approved = 0 WHERE id = ?');
    $stmt->bind_param('i', $postId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}
