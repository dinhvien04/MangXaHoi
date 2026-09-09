<?php

function checkAdminUser($loginData)
{
    global $db;
    $email = normalizeEmail($loginData['email'] ?? '');
    $plainPassword = (string) ($loginData['password'] ?? '');
    $data = ['status' => false, 'user' => []];

    if ($email === '' || $plainPassword === '') {
        return $data;
    }
    if (!consumeRateLimit('admin_login', $email . '|' . clientIp(), 20, 900)) {
        return $data;
    }

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND role = 'Admin' AND ac_status = 1 LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user || !passwordMatches($plainPassword, $user['password'])) {
        return $data;
    }

    $user['password'] = upgradePasswordHash((int) $user['id'], $plainPassword, $user['password']);
    $user['password_text'] = '';
    $data['status'] = true;
    $data['user'] = $user;
    $data['user_id'] = (int) $user['id'];
    $data['role'] = 'Admin';
    return $data;
}

function getAdmin($userId)
{
    global $db;
    $userId = (int) $userId;
    if ($userId <= 0) {
        return null;
    }
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND role = 'Admin' AND ac_status = 1 LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function getUsersList($searchKeyword = '', $limit = 100, $offset = 0)
{
    global $db;
    $searchKeyword = trim((string) $searchKeyword);
    $limit = max(1, min(200, (int) $limit));
    $offset = max(0, (int) $offset);

    if ($searchKeyword === '') {
        $stmt = $db->prepare('SELECT * FROM users ORDER BY id DESC LIMIT ? OFFSET ?');
        $stmt->bind_param('ii', $limit, $offset);
    } else {
        $like = '%' . $searchKeyword . '%';
        $stmt = $db->prepare('SELECT * FROM users WHERE first_name LIKE ? OR last_name LIKE ? OR username LIKE ? OR email LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?');
        $stmt->bind_param('ssssii', $like, $like, $like, $like, $limit, $offset);
    }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function loginUserByAdmin($userId)
{
    global $db;
    if (!validateAdminSession()['ok']) {
        return ['status' => false, 'user' => []];
    }
    $userId = (int) $userId;
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND role = 'User' AND ac_status = 1 LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $user ? ['status' => true, 'user' => $user] : ['status' => false, 'user' => []];
}

function totalCommentsCount()
{
    global $db;
    $row = $db->query('SELECT COUNT(*) AS row FROM comments')->fetch_assoc();
    return (int) ($row['row'] ?? 0);
}

function totalPostsCount()
{
    global $db;
    $row = $db->query('SELECT COUNT(*) AS row FROM posts')->fetch_assoc();
    return (int) ($row['row'] ?? 0);
}

function totalUsersCount()
{
    global $db;
    $row = $db->query('SELECT COUNT(*) AS row FROM users')->fetch_assoc();
    return (int) ($row['row'] ?? 0);
}

function totalLikesCount()
{
    global $db;
    $row = $db->query('SELECT COUNT(*) AS row FROM likes')->fetch_assoc();
    return (int) ($row['row'] ?? 0);
}

function blockUserByAdmin($userId)
{
    global $db;
    $admin = requireAdminAuth();
    $userId = (int) $userId;
    if ($userId <= 0 || $userId === (int) $admin['id']) {
        return false;
    }
    $stmt = $db->prepare("UPDATE users SET ac_status = 2 WHERE id = ? AND role = 'User' AND ac_status != 2");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected > 0;
}

function unblockUserByAdmin($userId)
{
    global $db;
    $admin = requireAdminAuth();
    $userId = (int) $userId;
    if ($userId <= 0 || $userId === (int) $admin['id']) {
        return false;
    }
    $stmt = $db->prepare("UPDATE users SET ac_status = 1 WHERE id = ? AND role = 'User' AND ac_status = 2");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected > 0;
}

function updateAdmin($data)
{
    global $db;
    $admin = requireAdminAuth();
    $userId = (int) $admin['id'];
    $firstName = trim((string) ($data['first_name'] ?? ''));
    $lastName = trim((string) ($data['last_name'] ?? ''));
    $email = normalizeEmail($data['email'] ?? '');
    $providedPassword = (string) ($data['password'] ?? '');

    if ($firstName === '' || $lastName === '' || mb_strlen($firstName) > 100 || mb_strlen($lastName) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    if ($providedPassword !== '' && strlen($providedPassword) < 8) {
        return false;
    }

    $stmt = $db->prepare('SELECT COUNT(*) AS row FROM users WHERE email = ? AND id != ?');
    $stmt->bind_param('si', $email, $userId);
    $stmt->execute();
    $duplicate = (int) ($stmt->get_result()->fetch_assoc()['row'] ?? 0) > 0;
    $stmt->close();
    if ($duplicate) {
        return false;
    }

    $password = $admin['password'];
    if ($providedPassword !== '') {
        $password = password_hash($providedPassword, PASSWORD_DEFAULT);
    }

    try {
        $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, password = ?, password_text = '', role = 'Admin' WHERE id = ?");
        $stmt->bind_param('ssssi', $firstName, $lastName, $email, $password, $userId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    } catch (Throwable $e) {
        return false;
    }
}

function getPosts($search = '', $reportStatus = '', $limit = 100, $offset = 0)
{
    global $db;
    $search = trim((string) $search);
    $reportStatus = (string) $reportStatus;
    $limit = max(1, min(200, (int) $limit));
    $offset = max(0, (int) $offset);

    $filter = '';
    if ($reportStatus === 'reported') {
        $filter = ' AND p.is_reported = 1';
    } elseif ($reportStatus === 'not_reported') {
        $filter = ' AND p.is_reported = 0';
    }

    if ($search !== '') {
        $query = "SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id WHERE (u.username LIKE CONCAT('%', ?, '%') OR p.post_text LIKE CONCAT('%', ?, '%'))" . $filter . ' ORDER BY p.id DESC LIMIT ? OFFSET ?';
        $stmt = $db->prepare($query);
        $stmt->bind_param('ssii', $search, $search, $limit, $offset);
    } else {
        $query = 'SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id WHERE 1' . $filter . ' ORDER BY p.id DESC LIMIT ? OFFSET ?';
        $stmt = $db->prepare($query);
        $stmt->bind_param('ii', $limit, $offset);
    }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function getLikesCount($postId)
{
    global $db;
    $postId = (int) $postId;
    if ($postId <= 0) return 0;
    $stmt = $db->prepare('SELECT COUNT(*) AS like_count FROM likes WHERE post_id = ?');
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['like_count'] ?? 0);
}

function getCommentsCount($postId)
{
    global $db;
    $postId = (int) $postId;
    if ($postId <= 0) return 0;
    $stmt = $db->prepare('SELECT COUNT(*) AS comment_count FROM comments WHERE post_id = ?');
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['comment_count'] ?? 0);
}

function getCommentsAdmin($postId, $limit = 100)
{
    global $db;
    $postId = (int) $postId;
    $limit = max(1, min(200, (int) $limit));
    if ($postId <= 0) return [];
    $stmt = $db->prepare('SELECT comments.id, comments.comment, comments.created_at, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE comments.post_id = ? ORDER BY comments.created_at DESC LIMIT ?');
    $stmt->bind_param('ii', $postId, $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function updateUserRoleByAdmin($userId, $role)
{
    global $db;
    $admin = requireAdminAuth();
    $userId = (int) $userId;
    $role = $role === 'Admin' ? 'Admin' : 'User';
    $target = $userId > 0 ? getUser($userId) : null;
    if (!$target || $userId === (int) $admin['id']) {
        return false;
    }
    if ($role === 'Admin' && (int) $target['ac_status'] !== 1) {
        return false;
    }
    $stmt = $db->prepare('UPDATE users SET role = ? WHERE id = ?');
    $stmt->bind_param('si', $role, $userId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected > 0;
}

function deleteUserByAdmin($userId)
{
    global $db;
    $admin = requireAdminAuth();
    $userId = (int) $userId;
    if ($userId <= 0 || $userId === (int) $admin['id']) {
        return false;
    }
    $target = getUser($userId);
    if (!$target || (string) $target['role'] === 'Admin') {
        return false;
    }
    $stmt = $db->prepare('SELECT post_img FROM posts WHERE user_id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $postImages = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'post_img');
    $stmt->close();

    $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role = 'User'");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    if ($affected > 0) {
        if (!empty($target['profile_pic']) && $target['profile_pic'] !== 'default_profile.jpg') {
            @unlink(dirname(__DIR__, 2) . '/public/images/profile/' . basename((string) $target['profile_pic']));
        }
        foreach ($postImages as $postImage) {
            if ($postImage !== '') {
                @unlink(dirname(__DIR__, 2) . '/public/images/posts/' . basename((string) $postImage));
            }
        }
    }
    return $affected > 0;
}

function deleteCommentByAdmin($commentId)
{
    global $db;
    requireAdminAuth();
    $commentId = (int) $commentId;
    if ($commentId <= 0) return false;
    $stmt = $db->prepare('DELETE FROM comments WHERE id = ?');
    $stmt->bind_param('i', $commentId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected > 0;
}

function approvePostByAdmin($postId)
{
    global $db;
    requireAdminAuth();
    $postId = (int) $postId;
    if ($postId <= 0) return false;
    $stmt = $db->prepare('UPDATE posts SET is_approved = 1, is_reported = 0 WHERE id = ? AND (is_reported = 1 OR is_approved = 0)');
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected > 0;
}
