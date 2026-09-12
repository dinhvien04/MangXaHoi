<?php

require_once __DIR__ . '/render.php';

if (isset($_GET['newfp'])) {
    unset($_SESSION['auth_temp'], $_SESSION['forgot_otp']);
}

$user = null;
$posts = [];
$followSuggestions = [];
if (!empty($_SESSION['Auth']) && !empty($_SESSION['userdata']['id'])) {
    $user = getUser($_SESSION['userdata']['id']);
    if (!$user || !in_array((string) $user['role'], ['User', 'Admin'], true)) {
        unset($_SESSION['Auth'], $_SESSION['userdata'], $_SESSION['email_otp']);
        $user = null;
    } else {
        $_SESSION['userdata'] = $user;
        if ((int) $user['ac_status'] === 1) {
            $posts = filterPosts(50, 0);
            $followSuggestions = filterFollowSuggestion();
        }
    }
}

$renderShell = function ($title, $body, array $bodyData = [], $withNavbar = false) use ($user) {
    renderView('layouts/header', ['pageTitle' => $title]);
    if ($withNavbar && $user) {
        renderView('layouts/navbar', ['user' => $user]);
    }
    renderView($body, $bodyData);
    renderView('layouts/footer', ['user' => $user]);
};

if ($user && (int) $user['ac_status'] === 2) {
    $renderShell('Tài khoản bị chặn', 'user/profile/blocked', ['user' => $user]);
} elseif ($user && (int) $user['ac_status'] === 0) {
    $renderShell('Xác minh Email của bạn', 'user/auth/verify-email', ['user' => $user]);
} elseif ($user && (int) $user['ac_status'] === 1 && isset($_GET['editprofile'])) {
    $renderShell('Chỉnh sửa hồ sơ', 'user/profile/edit-profile', ['user' => $user], true);
} elseif ($user && (int) $user['ac_status'] === 1 && isset($_GET['u'])) {
    $profile = getUserByUsername($_GET['u']);
    if (!$profile || (int) $profile['ac_status'] !== 1) {
        $renderShell('Không tìm thấy người dùng', 'user/profile/user-not-found', [], true);
    } else {
        $profilePosts = getPostById($profile['id'], 50, 0);
        $profile['followers'] = getFollowers($profile['id']);
        $profile['following'] = getFollowing($profile['id']);
        $renderShell($profile['first_name'] . ' ' . $profile['last_name'], 'user/profile/profile', [
            'user' => $user,
            'profile' => $profile,
            'profilePosts' => $profilePosts,
        ], true);
    }
} elseif ($user && (int) $user['ac_status'] === 1) {
    $renderShell('Trang chủ', 'user/posts/wall', [
        'user' => $user,
        'posts' => $posts,
        'followSuggestions' => $followSuggestions,
    ], true);
} elseif (isset($_GET['signup'])) {
    $renderShell('Handbook - Đăng ký', 'user/auth/signup');
} elseif (isset($_GET['forgotpassword'])) {
    $renderShell('Handbook - Quên mật khẩu', 'user/auth/forgot-password');
} else {
    $renderShell('Handbook - Đăng nhập', 'user/auth/login');
}

unset($_SESSION['error'], $_SESSION['formdata']);
