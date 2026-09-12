<?php

function ensureUserProfilePic(?array &$row): void
{
    if ($row) {
        $pic = basename((string) ($row['profile_pic'] ?? ''));
        if ($pic === '' || !is_file(dirname(__DIR__, 2) . '/public/images/profile/' . $pic)) {
            $row['profile_pic'] = 'default_profile.jpg';
        }
    }
}

function getUser($userId)
{
    global $db;
    $userId = (int) $userId;
    if ($userId <= 0) {
        return null;
    }
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    ensureUserProfilePic($row);
    return $row ?: null;
}

function getActiveUser($userId)
{
    global $db;
    $userId = (int) $userId;
    if ($userId <= 0) {
        return null;
    }
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND ac_status = 1 LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    ensureUserProfilePic($row);
    return $row ?: null;
}

function getUserByUsername($username)
{
    global $db;
    $username = trim((string) $username);
    if ($username === '') {
        return null;
    }
    $stmt = $db->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    ensureUserProfilePic($row);
    return $row ?: null;
}

function isUsernameRegisteredByOther($username)
{
    global $db;
    $userId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $username = trim((string) $username);
    $stmt = $db->prepare('SELECT COUNT(*) AS `row` FROM users WHERE username = ? AND id != ?');
    $stmt->bind_param('si', $username, $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['row'] ?? 0);
}

function validateUpdateForm($formData, $imageData)
{
    $firstName = trim((string) ($formData['first_name'] ?? ''));
    $lastName = trim((string) ($formData['last_name'] ?? ''));
    $username = trim((string) ($formData['username'] ?? ''));

    if ($firstName === '' || mb_strlen($firstName) > 100) {
        return ['status' => false, 'msg' => 'Vui lòng nhập họ hợp lệ', 'field' => 'first_name'];
    }
    if ($lastName === '' || mb_strlen($lastName) > 100) {
        return ['status' => false, 'msg' => 'Vui lòng nhập tên hợp lệ', 'field' => 'last_name'];
    }
    if (!preg_match('/^[A-Za-z0-9._]{3,30}$/', $username)) {
        return ['status' => false, 'msg' => 'Username phải dài 3-30 ký tự và chỉ gồm chữ, số, dấu chấm hoặc gạch dưới', 'field' => 'username'];
    }
    if (isUsernameRegisteredByOther($username)) {
        return ['status' => false, 'msg' => $username . ' đã được sử dụng', 'field' => 'username'];
    }
    if (!empty($formData['password']) && strlen((string) $formData['password']) < 8) {
        return ['status' => false, 'msg' => 'Mật khẩu phải có ít nhất 8 ký tự', 'field' => 'password'];
    }

    if (!empty($imageData['name'])) {
        if (($imageData['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || empty($imageData['tmp_name']) || !is_file($imageData['tmp_name'])) {
            return ['status' => false, 'msg' => 'Ảnh tải lên không hợp lệ', 'field' => 'profile_pic'];
        }
        if ((int) ($imageData['size'] ?? 0) <= 0 || (int) ($imageData['size'] ?? 0) > 1000000) {
            return ['status' => false, 'msg' => 'Ảnh đại diện phải nhỏ hơn 1 MB', 'field' => 'profile_pic'];
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($imageData['tmp_name']);
        if (!in_array($mime, ['image/jpeg', 'image/png'], true) || @getimagesize($imageData['tmp_name']) === false) {
            return ['status' => false, 'msg' => 'Chỉ cho phép hình ảnh JPG hoặc PNG hợp lệ', 'field' => 'profile_pic'];
        }
    }

    return ['status' => true];
}

function updateProfile($data, $imageData)
{
    global $db;
    $userId = (int) ($_SESSION['userdata']['id'] ?? 0);
    $current = getUser($userId);
    if (!$current || (int) $current['ac_status'] !== 1 || !in_array((string) $current['role'], ['User', 'Admin'], true)) {
        return false;
    }

    $firstName = trim((string) ($data['first_name'] ?? ''));
    $lastName = trim((string) ($data['last_name'] ?? ''));
    $username = trim((string) ($data['username'] ?? ''));
    $password = (string) $current['password'];
    if (!empty($data['password'])) {
        $password = password_hash((string) $data['password'], PASSWORD_DEFAULT);
    }

    $newProfilePic = null;
    if (!empty($imageData['name'])) {
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($imageData['tmp_name']);
        $extension = $mime === 'image/png' ? 'png' : 'jpg';
        $newProfilePic = bin2hex(random_bytes(16)) . '.' . $extension;
        $imageDir = dirname(__DIR__, 2) . '/public/images/profile';
        if (!is_dir($imageDir) && !mkdir($imageDir, 0775, true) && !is_dir($imageDir)) {
            return false;
        }
        if (!move_uploaded_file($imageData['tmp_name'], $imageDir . '/' . $newProfilePic)) {
            return false;
        }
    }

    try {
        if ($newProfilePic !== null) {
            $stmt = $db->prepare('UPDATE users SET first_name = ?, last_name = ?, username = ?, password = ?, profile_pic = ? WHERE id = ?');
            $stmt->bind_param('sssssi', $firstName, $lastName, $username, $password, $newProfilePic, $userId);
        } else {
            $stmt = $db->prepare('UPDATE users SET first_name = ?, last_name = ?, username = ?, password = ? WHERE id = ?');
            $stmt->bind_param('ssssi', $firstName, $lastName, $username, $password, $userId);
        }
        $ok = $stmt->execute();
        $stmt->close();
    } catch (Throwable $e) {
        if ($newProfilePic !== null) {
            @unlink(dirname(__DIR__, 2) . '/public/images/profile/' . $newProfilePic);
        }
        return false;
    }

    if (!$ok) {
        return false;
    }

    if ($newProfilePic !== null && !empty($current['profile_pic']) && $current['profile_pic'] !== 'default_profile.jpg') {
        @unlink(dirname(__DIR__, 2) . '/public/images/profile/' . basename((string) $current['profile_pic']));
    }
    $_SESSION['userdata'] = getUser($userId);
    return true;
}
