<?php

function checkAdminUser($loginData)
{
    global $db;
    $email = trim((string) ($loginData['email'] ?? ''));
    $plainPassword = (string) ($loginData['password'] ?? '');
    $data = ['status' => false, 'user' => []];

    if ($email === '' || $plainPassword === '') {
        return $data;
    }

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND role = 'Admin' LIMIT 1");
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
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND role = 'Admin' LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function getUsersList($searchKeyword = '')
{
    global $db;
    $searchKeyword = trim((string) $searchKeyword);

    if ($searchKeyword === '') {
        $result = $db->query("SELECT * FROM users ORDER BY id DESC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    $like = '%' . $searchKeyword . '%';
    $stmt = $db->prepare("SELECT * FROM users WHERE first_name LIKE ? OR last_name LIKE ? OR username LIKE ? OR email LIKE ? ORDER BY id DESC");
    $stmt->bind_param('ssss', $like, $like, $like, $like);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function loginUserByAdmin($email)
{
    global $db;
    $email = trim((string) $email);
    $data = ['status' => false, 'user' => []];

    if (empty($_SESSION['admin_auth'])) {
        return $data;
    }

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user) {
        $data['status'] = true;
        $data['user'] = $user;
    }
    return $data;
}

function totalCommentsCount()
{
    global $db;
    $row = $db->query("SELECT COUNT(*) AS row FROM comments")->fetch_assoc();
    return (int) ($row['row'] ?? 0);
}

function totalPostsCount()
{
    global $db;
    $row = $db->query("SELECT COUNT(*) AS row FROM posts")->fetch_assoc();
    return (int) ($row['row'] ?? 0);
}

function totalUsersCount()
{
    global $db;
    $row = $db->query("SELECT COUNT(*) AS row FROM users")->fetch_assoc();
    return (int) ($row['row'] ?? 0);
}

function totalLikesCount()
{
    global $db;
    $row = $db->query("SELECT COUNT(*) AS row FROM likes")->fetch_assoc();
    return (int) ($row['row'] ?? 0);
}

function blockUserByAdmin($userId)
{
    global $db;
    if (empty($_SESSION['admin_auth'])) {
        return false;
    }
    $userId = (int) $userId;
    if ($userId <= 0 || $userId === (int) $_SESSION['admin_auth']) {
        return false;
    }
    $stmt = $db->prepare("UPDATE users SET ac_status = 2 WHERE id = ? AND role != 'Admin'");
    $stmt->bind_param('i', $userId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function unblockUserByAdmin($userId)
{
    global $db;
    if (empty($_SESSION['admin_auth'])) {
        return false;
    }
    $userId = (int) $userId;
    $stmt = $db->prepare("UPDATE users SET ac_status = 1 WHERE id = ?");
    $stmt->bind_param('i', $userId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function updateAdmin($data)
{
    global $db;
    if (empty($_SESSION['admin_auth'])) {
        return false;
    }

    $userId = (int) $_SESSION['admin_auth'];
    $current = getAdmin($userId);
    if (!$current) {
        return false;
    }

    $firstName = trim((string) ($data['first_name'] ?? ''));
    $lastName = trim((string) ($data['last_name'] ?? ''));
    $email = trim((string) ($data['email'] ?? ''));
    $providedPassword = (string) ($data['password'] ?? '');

    if ($firstName === '' || $lastName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $password = $current['password'];
    if ($providedPassword !== '' && !hash_equals((string) $current['password'], $providedPassword)) {
        $password = password_hash($providedPassword, PASSWORD_DEFAULT);
    }

    $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, password = ?, password_text = '', role = 'Admin' WHERE id = ?");
    $stmt->bind_param('ssssi', $firstName, $lastName, $email, $password, $userId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function getPosts($search = '', $reportStatus = '')
{
    global $db;
    $search = trim((string) $search);
    $reportStatus = (string) $reportStatus;

    $query = "SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id WHERE 1";
    if ($search !== '') {
        $query .= " AND (u.username LIKE CONCAT('%', ?, '%') OR p.post_text LIKE CONCAT('%', ?, '%'))";
    }
    if ($reportStatus === 'reported') {
        $query .= " AND p.is_reported = 1";
    } elseif ($reportStatus === 'not_reported') {
        $query .= " AND p.is_reported = 0";
    }
    $query .= " ORDER BY p.id DESC";

    $stmt = $db->prepare($query);
    if ($search !== '') {
        $stmt->bind_param('ss', $search, $search);
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
    if ($postId <= 0) {
        return 0;
    }
    $stmt = $db->prepare("SELECT COUNT(*) AS like_count FROM likes WHERE post_id = ?");
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
    if ($postId <= 0) {
        return 0;
    }
    $stmt = $db->prepare("SELECT COUNT(*) AS comment_count FROM comments WHERE post_id = ?");
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['comment_count'] ?? 0);
}

function getCommentsAdmin($postId)
{
    global $db;
    $postId = (int) $postId;
    if ($postId <= 0) {
        return [];
    }

    $stmt = $db->prepare("SELECT comments.id, comments.comment, comments.created_at, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE comments.post_id = ? ORDER BY comments.created_at DESC");
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function updateUserRoleByAdmin($userId, $role)
{
    global $db;
    requireAdminAuth();
    $userId = (int) $userId;
    $role = $role === 'Admin' ? 'Admin' : 'User';
    if ($userId <= 0 || $userId === (int) $_SESSION['admin_auth']) {
        return false;
    }
    $stmt = $db->prepare('UPDATE users SET role = ? WHERE id = ?');
    $stmt->bind_param('si', $role, $userId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function deleteUserByAdmin($userId)
{
    global $db;
    requireAdminAuth();
    $userId = (int) $userId;
    if ($userId <= 0 || $userId === (int) $_SESSION['admin_auth']) {
        return false;
    }
    $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role != 'Admin'");
    $stmt->bind_param('i', $userId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function deleteCommentByAdmin($commentId)
{
    global $db;
    requireAdminAuth();
    $commentId = (int) $commentId;
    if ($commentId <= 0) {
        return false;
    }
    $stmt = $db->prepare('DELETE FROM comments WHERE id = ?');
    $stmt->bind_param('i', $commentId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function approvePostByAdmin($postId)
{
    global $db;
    requireAdminAuth();
    $postId = (int) $postId;
    if ($postId <= 0) {
        return false;
    }
    $stmt = $db->prepare('UPDATE posts SET is_approved = 1, is_reported = 0 WHERE id = ?');
    $stmt->bind_param('i', $postId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}
