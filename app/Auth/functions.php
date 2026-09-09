<?php

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

function validateSignupForm($formData)
{
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

    return ['status' => true];
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
