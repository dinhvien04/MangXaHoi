<?php

function normalizeEmail($email)
{
    return strtolower(trim((string) $email));
}

function isEmailRegistered($email)
{
    global $db;
    $email = normalizeEmail($email);
    $stmt = $db->prepare('SELECT COUNT(*) AS `row` FROM users WHERE email = ?');
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
    $stmt = $db->prepare('SELECT COUNT(*) AS `row` FROM users WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['row'] ?? 0);
}

function validateSignupForm($formData)
{
    $firstName = trim((string) ($formData['first_name'] ?? ''));
    $lastName = trim((string) ($formData['last_name'] ?? ''));
    $email = normalizeEmail($formData['email'] ?? '');
    $username = trim((string) ($formData['username'] ?? ''));
    $password = (string) ($formData['password'] ?? '');

    if ($firstName === '' || mb_strlen($firstName) > 100) {
        return ['status' => false, 'msg' => 'Vui lòng nhập họ hợp lệ', 'field' => 'first_name'];
    }
    if ($lastName === '' || mb_strlen($lastName) > 100) {
        return ['status' => false, 'msg' => 'Vui lòng nhập tên hợp lệ', 'field' => 'last_name'];
    }
    if ($email === '' || strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['status' => false, 'msg' => 'Vui lòng nhập email hợp lệ', 'field' => 'email'];
    }
    if (!preg_match('/^[A-Za-z0-9._]{3,30}$/', $username)) {
        return ['status' => false, 'msg' => 'Username phải dài 3-30 ký tự và chỉ gồm chữ, số, dấu chấm hoặc gạch dưới', 'field' => 'username'];
    }
    if (strlen($password) < 8) {
        return ['status' => false, 'msg' => 'Mật khẩu phải có ít nhất 8 ký tự', 'field' => 'password'];
    }
    if (isEmailRegistered($email)) {
        return ['status' => false, 'msg' => 'Email đã được đăng ký', 'field' => 'email'];
    }
    if (isUsernameRegistered($username)) {
        return ['status' => false, 'msg' => 'Username đã được đăng ký', 'field' => 'username'];
    }

    return ['status' => true];
}

function validateLoginForm($formData)
{
    if (trim((string) ($formData['username_email'] ?? '')) === '') {
        return ['status' => false, 'msg' => 'Vui lòng nhập username/email', 'field' => 'username_email'];
    }
    if ((string) ($formData['password'] ?? '') === '') {
        return ['status' => false, 'msg' => 'Vui lòng nhập mật khẩu', 'field' => 'password'];
    }

    $subject = strtolower(trim((string) ($formData['username_email'] ?? ''))) . '|' . clientIp();
    if (!consumeRateLimit('login', $subject, 30, 900)) {
        return ['status' => false, 'msg' => 'Bạn đã thử đăng nhập quá nhiều lần. Vui lòng thử lại sau.', 'field' => 'checkuser'];
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
    $firstName = trim((string) ($data['first_name'] ?? ''));
    $lastName = trim((string) ($data['last_name'] ?? ''));
    $email = normalizeEmail($data['email'] ?? '');
    $username = trim((string) ($data['username'] ?? ''));
    $genderValue = (int) ($data['gender'] ?? 1);
    $gender = $genderValue === 2 ? 'Female' : ($genderValue === 3 ? 'Others' : 'Male');
    $passwordHash = password_hash((string) ($data['password'] ?? ''), PASSWORD_DEFAULT);
    $role = 'User';
    $passwordText = '';
    $acStatus = 0;

    try {
        $stmt = $db->prepare('INSERT INTO users (first_name, last_name, gender, email, username, password, password_text, role, ac_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssssssi', $firstName, $lastName, $gender, $email, $username, $passwordHash, $passwordText, $role, $acStatus);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    } catch (Throwable $e) {
        error_log('Handbook create user error: ' . $e->getMessage());
        return false;
    }
}

function verifyEmail($email)
{
    global $db;
    $email = normalizeEmail($email);
    $stmt = $db->prepare('UPDATE users SET ac_status = 1 WHERE email = ? AND ac_status = 0');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected > 0;
}

function resetPassword($email, $password)
{
    global $db;
    $email = normalizeEmail($email);
    if (strlen((string) $password) < 8) {
        return false;
    }
    $passwordHash = password_hash((string) $password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password = ?, password_text = '' WHERE email = ?");
    $stmt->bind_param('ss', $passwordHash, $email);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected > 0;
}

function sendOtpToSession($sessionKey, $email, $purpose, $subject, $enforceCooldown = true)
{
    $email = normalizeEmail($email);
    $existing = $_SESSION[$sessionKey] ?? null;
    if ($enforceCooldown && is_array($existing) && !canResendOtp($existing)) {
        return ['status' => false, 'reason' => 'cooldown'];
    }
    if (!consumeRateLimit('otp_send_' . $purpose, $email . '|' . clientIp(), 5, 900)) {
        return ['status' => false, 'reason' => 'rate_limit'];
    }

    $code = random_int(100000, 999999);
    if (!sendCode($email, $subject, $code)) {
        return ['status' => false, 'reason' => 'mail'];
    }
    $_SESSION[$sessionKey] = buildOtpState($email, $code, $purpose);
    return ['status' => true, 'reason' => null];
}
