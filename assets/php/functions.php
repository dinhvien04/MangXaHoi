<?php
require_once 'config.php';

$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME) or die("cơ sở dữ liệu không được kết nối");
$db->set_charset('utf8mb4');

function showPage($page, $data = "")
{
    include("assets/pages/$page.php");
}

function passwordMatches($plainPassword, $storedPassword)
{
    $storedPassword = (string) $storedPassword;
    if ($storedPassword === '') {
        return false;
    }

    $info = password_get_info($storedPassword);
    if (($info['algoName'] ?? 'unknown') !== 'unknown') {
        return password_verify($plainPassword, $storedPassword);
    }

    return hash_equals($storedPassword, (string) $plainPassword)
        || hash_equals($storedPassword, md5((string) $plainPassword));
}

function upgradePasswordHash($userId, $plainPassword, $storedPassword)
{
    global $db;

    $info = password_get_info((string) $storedPassword);
    if (($info['algoName'] ?? 'unknown') !== 'unknown') {
        return (string) $storedPassword;
    }

    $newHash = password_hash((string) $plainPassword, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password = ?, password_text = '' WHERE id = ?");
    $stmt->bind_param('si', $newHash, $userId);
    $stmt->execute();
    $stmt->close();

    return $newHash;
}

function getActiveChatUserIds()
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $stmt = $db->prepare("SELECT from_user_id, to_user_id FROM messages WHERE to_user_id = ? OR from_user_id = ? ORDER BY id DESC");
    $stmt->bind_param('ii', $currentUserId, $currentUserId);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $ids = [];
    foreach ($data as $chat) {
        $from = (int) $chat['from_user_id'];
        $to = (int) $chat['to_user_id'];
        if ($from !== $currentUserId && !in_array($from, $ids, true)) {
            $ids[] = $from;
        }
        if ($to !== $currentUserId && !in_array($to, $ids, true)) {
            $ids[] = $to;
        }
    }

    return $ids;
}

function getMessages($userId)
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $userId = (int) $userId;
    $stmt = $db->prepare("SELECT * FROM messages WHERE (to_user_id = ? AND from_user_id = ?) OR (from_user_id = ? AND to_user_id = ?) ORDER BY id DESC");
    $stmt->bind_param('iiii', $currentUserId, $userId, $currentUserId, $userId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function sendMessage($userId, $msg)
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $userId = (int) $userId;
    $msg = trim((string) $msg);
    if ($msg === '' || $userId <= 0) {
        return false;
    }

    $stmt = $db->prepare("INSERT INTO messages (from_user_id, to_user_id, msg) VALUES (?, ?, ?)");
    $stmt->bind_param('iis', $currentUserId, $userId, $msg);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function newMsgCount()
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $stmt = $db->prepare("SELECT COUNT(*) AS row FROM messages WHERE to_user_id = ? AND read_status = 0");
    $stmt->bind_param('i', $currentUserId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['row'] ?? 0);
}

function updateMessageReadStatus($userId)
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $userId = (int) $userId;
    $stmt = $db->prepare("UPDATE messages SET read_status = 1 WHERE to_user_id = ? AND from_user_id = ?");
    $stmt->bind_param('ii', $currentUserId, $userId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function gettime($date)
{
    $weekdays = [
        'Monday' => 'Thứ Hai',
        'Tuesday' => 'Thứ Ba',
        'Wednesday' => 'Thứ Tư',
        'Thursday' => 'Thứ Năm',
        'Friday' => 'Thứ Sáu',
        'Saturday' => 'Thứ Bảy',
        'Sunday' => 'Chủ Nhật',
    ];

    $timestamp = strtotime($date);
    $weekday = $weekdays[date('l', $timestamp)] ?? '';
    return date('H:i', $timestamp) . " - $weekday, " . date('d/m/Y', $timestamp);
}

function getAllMessages()
{
    $conversation = [];
    foreach (getActiveChatUserIds() as $index => $id) {
        $conversation[$index]['user_id'] = $id;
        $conversation[$index]['messages'] = getMessages($id);
    }
    return $conversation;
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

function blockUser($blockedUserId)
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $blockedUserId = (int) $blockedUserId;
    if ($blockedUserId <= 0 || $blockedUserId === $currentUserId || checkBlockStatus($currentUserId, $blockedUserId)) {
        return false;
    }

    $db->begin_transaction();
    try {
        $stmt = $db->prepare("INSERT INTO block_list (user_id, blocked_user_id) VALUES (?, ?)");
        $stmt->bind_param('ii', $currentUserId, $blockedUserId);
        $stmt->execute();
        $stmt->close();

        $stmt = $db->prepare("DELETE FROM follow_list WHERE (follower_id = ? AND user_id = ?) OR (follower_id = ? AND user_id = ?)");
        $stmt->bind_param('iiii', $currentUserId, $blockedUserId, $blockedUserId, $currentUserId);
        $stmt->execute();
        $stmt->close();

        createNotification($currentUserId, $blockedUserId, 'đã chặn bạn');
        $db->commit();
        return true;
    } catch (Throwable $e) {
        $db->rollback();
        return false;
    }
}

function unblockUser($userId)
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $userId = (int) $userId;
    $stmt = $db->prepare("DELETE FROM block_list WHERE user_id = ? AND blocked_user_id = ?");
    $stmt->bind_param('ii', $currentUserId, $userId);
    $ok = $stmt->execute();
    $stmt->close();
    if ($ok) {
        createNotification($currentUserId, $userId, 'đã bỏ chặn bạn!');
    }
    return $ok;
}

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

function createNotification($fromUserId, $toUserId, $msg, $postId = 0)
{
    global $db;
    $fromUserId = (int) $fromUserId;
    $toUserId = (int) $toUserId;
    $postId = (int) $postId;
    $msg = (string) $msg;
    $stmt = $db->prepare("INSERT INTO notifications (from_user_id, to_user_id, message, post_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('iisi', $fromUserId, $toUserId, $msg, $postId);
    $ok = $stmt->execute();
    $stmt->close();
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

function getNotifications()
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $stmt = $db->prepare("SELECT * FROM notifications WHERE to_user_id = ? ORDER BY id DESC");
    $stmt->bind_param('i', $currentUserId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function getUnreadNotificationsCount()
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $stmt = $db->prepare("SELECT COUNT(*) AS row FROM notifications WHERE to_user_id = ? AND read_status = 0");
    $stmt->bind_param('i', $currentUserId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['row'] ?? 0);
}

function show_time($time)
{
    return '<time style="font-size:small" class="timeago text-muted text-small" datetime="' . htmlspecialchars((string) $time, ENT_QUOTES, 'UTF-8') . '"></time>';
}

function setNotificationStatusAsRead()
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $stmt = $db->prepare("UPDATE notifications SET read_status = 1 WHERE to_user_id = ?");
    $stmt->bind_param('i', $currentUserId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
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

function showError($field)
{
    if (isset($_SESSION['error'])) {
        $error = $_SESSION['error'];
        if (isset($error['field']) && $field === $error['field']) {
            $msg = htmlspecialchars((string) ($error['msg'] ?? ''), ENT_QUOTES, 'UTF-8');
            echo '<div class="alert alert-danger my-2" role="alert">' . $msg . '</div>';
        }
    }
}

function showFormData($field)
{
    if (isset($_SESSION['formdata'][$field])) {
        return htmlspecialchars((string) $_SESSION['formdata'][$field], ENT_QUOTES, 'UTF-8');
    }
    return '';
}

function isEmailRegistered($email)
{
    global $db;
    $email = trim((string) $email);
    $stmt = $db->prepare("SELECT COUNT(*) AS row FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['row'] ?? 0);
}

function isUsernameRegistered($username)
{
    global $db;
    $username = trim((string) $username);
    $stmt = $db->prepare("SELECT COUNT(*) AS row FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['row'] ?? 0);
}

function isUsernameRegisteredByOther($username)
{
    global $db;
    $userId = (int) $_SESSION['userdata']['id'];
    $username = trim((string) $username);
    $stmt = $db->prepare("SELECT COUNT(*) AS row FROM users WHERE username = ? AND id != ?");
    $stmt->bind_param('si', $username, $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['row'] ?? 0);
}

function validateSignupForm($formData)
{
    $response = ['status' => true];

    if (empty($formData['first_name'])) {
        return ['status' => false, 'msg' => 'Vui lòng nhập họ', 'field' => 'first_name'];
    }
    if (empty($formData['last_name'])) {
        return ['status' => false, 'msg' => 'Vui lòng nhập tên', 'field' => 'last_name'];
    }
    if (empty($formData['email']) || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        return ['status' => false, 'msg' => 'Vui lòng nhập email hợp lệ', 'field' => 'email'];
    }
    if (empty($formData['username'])) {
        return ['status' => false, 'msg' => 'Vui lòng nhập username', 'field' => 'username'];
    }
    if (empty($formData['password'])) {
        return ['status' => false, 'msg' => 'Vui lòng nhập mật khẩu', 'field' => 'password'];
    }
    if (strlen((string) $formData['password']) < 6) {
        return ['status' => false, 'msg' => 'Mật khẩu phải có ít nhất 6 ký tự', 'field' => 'password'];
    }
    if (isEmailRegistered($formData['email'])) {
        return ['status' => false, 'msg' => 'Email đã được đăng ký', 'field' => 'email'];
    }
    if (isUsernameRegistered($formData['username'])) {
        return ['status' => false, 'msg' => 'Username đã được đăng ký', 'field' => 'username'];
    }

    return $response;
}

function validateLoginForm($formData)
{
    if (empty($formData['username_email'])) {
        return ['status' => false, 'msg' => 'Vui lòng nhập username/email', 'field' => 'username_email'];
    }
    if (empty($formData['password'])) {
        return ['status' => false, 'msg' => 'Vui lòng nhập mật khẩu', 'field' => 'password'];
    }

    $auth = checkUser($formData);
    if (!$auth['status']) {
        return ['status' => false, 'msg' => 'Tên đăng nhập/email hoặc mật khẩu không đúng', 'field' => 'checkuser'];
    }

    return ['status' => true, 'user' => $auth['user']];
}

function getUser($userId)
{
    global $db;
    $userId = (int) $userId;
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
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

function checkBlockStatus($currentUser, $userId)
{
    global $db;
    $currentUser = (int) $currentUser;
    $userId = (int) $userId;
    $stmt = $db->prepare("SELECT COUNT(*) AS row FROM block_list WHERE user_id = ? AND blocked_user_id = ?");
    $stmt->bind_param('ii', $currentUser, $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['row'] ?? 0);
}

function checkBS($userId)
{
    global $db;
    $currentUserId = (int) $_SESSION['userdata']['id'];
    $userId = (int) $userId;
    $stmt = $db->prepare("SELECT COUNT(*) AS row FROM block_list WHERE (user_id = ? AND blocked_user_id = ?) OR (user_id = ? AND blocked_user_id = ?)");
    $stmt->bind_param('iiii', $currentUserId, $userId, $userId, $currentUserId);
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

function searchUser($keyword)
{
    global $db;
    $keyword = trim((string) $keyword);
    if ($keyword === '') {
        return [];
    }
    $like = '%' . $keyword . '%';
    $stmt = $db->prepare("SELECT * FROM users WHERE role = 'User' AND (username LIKE ? OR first_name LIKE ? OR last_name LIKE ?) LIMIT 5");
    $stmt->bind_param('sss', $like, $like, $like);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function getUserByUsername($username)
{
    global $db;
    $username = trim((string) $username);
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function getPost()
{
    global $db;
    $query = "SELECT users.id AS uid, posts.id, posts.user_id, posts.post_img, posts.post_text, posts.created_at, users.first_name, users.last_name, users.username, users.profile_pic FROM posts JOIN users ON users.id = posts.user_id ORDER BY posts.id DESC";
    $run = mysqli_query($db, $query);
    return mysqli_fetch_all($run, MYSQLI_ASSOC);
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

function checkUser($loginData)
{
    global $db;
    $usernameEmail = trim((string) ($loginData['username_email'] ?? ''));
    $plainPassword = (string) ($loginData['password'] ?? '');
    $data = ['status' => false, 'user' => []];

    if ($usernameEmail === '' || $plainPassword === '') {
        return $data;
    }

    $stmt = $db->prepare("SELECT * FROM users WHERE (email = ? OR username = ?) AND role = 'User' LIMIT 1");
    $stmt->bind_param('ss', $usernameEmail, $usernameEmail);
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
    $data['role'] = $user['role'];
    return $data;
}

function createUser($data)
{
    global $db;
    $firstName = trim((string) $data['first_name']);
    $lastName = trim((string) $data['last_name']);
    $email = trim((string) $data['email']);
    $username = trim((string) $data['username']);
    $genderValue = (int) ($data['gender'] ?? 1);
    $gender = $genderValue === 2 ? 'Female' : ($genderValue === 3 ? 'Others' : 'Male');
    $passwordHash = password_hash((string) $data['password'], PASSWORD_DEFAULT);
    $role = 'User';
    $passwordText = '';
    $acStatus = 0;

    $stmt = $db->prepare("INSERT INTO users (first_name, last_name, gender, email, username, password, password_text, role, ac_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssssssi', $firstName, $lastName, $gender, $email, $username, $passwordHash, $passwordText, $role, $acStatus);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function verifyEmail($email)
{
    global $db;
    $email = trim((string) $email);
    $stmt = $db->prepare("UPDATE users SET ac_status = 1 WHERE email = ?");
    $stmt->bind_param('s', $email);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function resetPassword($email, $password)
{
    global $db;
    $email = trim((string) $email);
    $passwordHash = password_hash((string) $password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password = ?, password_text = '' WHERE email = ?");
    $stmt->bind_param('ss', $passwordHash, $email);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function validateUpdateForm($formData, $imageData)
{
    if (empty($formData['first_name'])) {
        return ['status' => false, 'msg' => 'Vui lòng nhập họ', 'field' => 'first_name'];
    }
    if (empty($formData['last_name'])) {
        return ['status' => false, 'msg' => 'Vui lòng nhập tên', 'field' => 'last_name'];
    }
    if (empty($formData['username'])) {
        return ['status' => false, 'msg' => 'Vui lòng nhập username', 'field' => 'username'];
    }
    if (isUsernameRegisteredByOther($formData['username'])) {
        return ['status' => false, 'msg' => $formData['username'] . ' đã được sử dụng', 'field' => 'username'];
    }
    if (!empty($formData['password']) && strlen((string) $formData['password']) < 6) {
        return ['status' => false, 'msg' => 'Mật khẩu phải có ít nhất 6 ký tự', 'field' => 'password'];
    }

    if (!empty($imageData['name'])) {
        $image = basename((string) $imageData['name']);
        $type = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        $size = ((int) $imageData['size']) / 1000;
        if (!in_array($type, ['jpg', 'jpeg', 'png'], true)) {
            return ['status' => false, 'msg' => 'Chỉ cho phép hình ảnh jpg, jpeg, png', 'field' => 'profile_pic'];
        }
        if ($size > 1000) {
            return ['status' => false, 'msg' => 'Tải lên hình ảnh có kích thước nhỏ hơn 1 MB', 'field' => 'profile_pic'];
        }
    }

    return ['status' => true];
}

function updateProfile($data, $imageData)
{
    global $db;
    $userId = (int) $_SESSION['userdata']['id'];
    $firstName = trim((string) $data['first_name']);
    $lastName = trim((string) $data['last_name']);
    $username = trim((string) $data['username']);
    $password = (string) $_SESSION['userdata']['password'];

    if (!empty($data['password'])) {
        $password = password_hash((string) $data['password'], PASSWORD_DEFAULT);
    }

    $profilePic = null;
    if (!empty($imageData['name'])) {
        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename((string) $imageData['name']));
        $imageName = time() . '_' . $safeName;
        $imageDir = "../images/profile/$imageName";
        if (!move_uploaded_file($imageData['tmp_name'], $imageDir)) {
            return false;
        }
        $profilePic = $imageName;
    }

    if ($profilePic !== null) {
        $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, username = ?, password = ?, password_text = '', profile_pic = ? WHERE id = ?");
        $stmt->bind_param('sssssi', $firstName, $lastName, $username, $password, $profilePic, $userId);
    } else {
        $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, username = ?, password = ?, password_text = '' WHERE id = ?");
        $stmt->bind_param('ssssi', $firstName, $lastName, $username, $password, $userId);
    }

    $ok = $stmt->execute();
    $stmt->close();
    if ($ok) {
        $_SESSION['userdata'] = getUser($userId);
    }
    return $ok;
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
    $imageDir = "../images/posts/$imageName";
    if (!move_uploaded_file($image['tmp_name'], $imageDir)) {
        return false;
    }

    $stmt = $db->prepare("INSERT INTO posts (user_id, post_text, post_img) VALUES (?, ?, ?)");
    $stmt->bind_param('iss', $userId, $postText, $imageName);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

?>
