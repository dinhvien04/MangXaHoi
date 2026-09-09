<?php

function getUser($userId)
{
    global $db;
    $userId = (int) $userId;
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function getUserByUsername($username)
{
    global $db;
    $username = trim((string) $username);
    $stmt = $db->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function isUsernameRegisteredByOther($username)
{
    global $db;
    $userId = (int) $_SESSION['userdata']['id'];
    $username = trim((string) $username);
    $stmt = $db->prepare('SELECT COUNT(*) AS row FROM users WHERE username = ? AND id != ?');
    $stmt->bind_param('si', $username, $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['row'] ?? 0);
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
        if (($imageData['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || empty($imageData['tmp_name'])) {
            return ['status' => false, 'msg' => 'Ảnh tải lên không hợp lệ', 'field' => 'profile_pic'];
        }
        if ((int) ($imageData['size'] ?? 0) > 1000000) {
            return ['status' => false, 'msg' => 'Tải lên hình ảnh có kích thước nhỏ hơn 1 MB', 'field' => 'profile_pic'];
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($imageData['tmp_name']);
        if (!in_array($mime, ['image/jpeg', 'image/png'], true)) {
            return ['status' => false, 'msg' => 'Chỉ cho phép hình ảnh jpg, jpeg, png', 'field' => 'profile_pic'];
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
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($imageData['tmp_name']);
        $extension = $mime === 'image/png' ? 'png' : 'jpg';
        $profilePic = bin2hex(random_bytes(12)) . '.' . $extension;
        $imageDir = dirname(__DIR__, 2) . '/public/images/profile';
        if (!is_dir($imageDir) && !mkdir($imageDir, 0775, true) && !is_dir($imageDir)) {
            return false;
        }
        if (!move_uploaded_file($imageData['tmp_name'], $imageDir . '/' . $profilePic)) {
            return false;
        }
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
