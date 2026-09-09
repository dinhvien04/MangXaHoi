<?php

function getPostById($userId)
{
    global $db;
    $userId = (int) $userId;
    $stmt = $db->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY id DESC");
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
    $stmt = $db->prepare("SELECT user_id FROM posts WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return isset($row['user_id']) ? (int) $row['user_id'] : 0;
}

function getPost()
{
    global $db;
    $query = "SELECT users.id AS uid, posts.id, posts.user_id, posts.post_img, posts.post_text, posts.created_at, users.first_name, users.last_name, users.username, users.profile_pic FROM posts JOIN users ON users.id = posts.user_id ORDER BY posts.id DESC";
    $run = mysqli_query($db, $query);
    return mysqli_fetch_all($run, MYSQLI_ASSOC);
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
        if (empty($_SESSION['userdata']['id'])) {
            return false;
        }
        $ownerId = getPosterId($postId);
        if ($ownerId !== (int) $_SESSION['userdata']['id']) {
            return false;
        }
    }

    $db->begin_transaction();
    try {
        $stmt = $db->prepare("DELETE FROM likes WHERE post_id = ?");
        $stmt->bind_param('i', $postId);
        $stmt->execute();
        $stmt->close();

        $stmt = $db->prepare("DELETE FROM comments WHERE post_id = ?");
        $stmt->bind_param('i', $postId);
        $stmt->execute();
        $stmt->close();

        $stmt = $db->prepare("DELETE FROM notifications WHERE post_id = ?");
        $stmt->bind_param('i', $postId);
        $stmt->execute();
        $stmt->close();

        $stmt = $db->prepare("DELETE FROM posts WHERE id = ?");
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

    $image = basename((string) $imageData['name']);
    $type = strtolower(pathinfo($image, PATHINFO_EXTENSION));
    $size = ((int) $imageData['size']) / 1000;

    if (!in_array($type, ['jpg', 'jpeg', 'png'], true)) {
        return ['status' => false, 'msg' => 'Chỉ cho phép hình ảnh jpg, jpeg, png', 'field' => 'post_img'];
    }
    if ($size > 2000) {
        return ['status' => false, 'msg' => 'Tải lên hình ảnh có kích thước nhỏ hơn 2 MB', 'field' => 'post_img'];
    }

    return ['status' => true];
}

function createPost($text, $image)
{
    global $db;
    $postText = trim((string) ($text['post_text'] ?? ''));
    $userId = (int) $_SESSION['userdata']['id'];

    $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename((string) $image['name']));
    $imageName = time() . '_' . $safeName;
    $imageDir = dirname(__DIR__, 2) . "/assets/images/posts/$imageName";
    if (!move_uploaded_file($image['tmp_name'], $imageDir)) {
        return false;
    }

    $stmt = $db->prepare("INSERT INTO posts (user_id, post_text, post_img) VALUES (?, ?, ?)");
    $stmt->bind_param('iss', $userId, $postText, $imageName);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}
