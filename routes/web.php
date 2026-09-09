<?php

if (isset($_GET['newfp'])) {
    unset($_SESSION['auth_temp'], $_SESSION['forgot_email'], $_SESSION['forgot_code']);
}

$user = null;
$posts = [];
$follow_suggestions = [];

if (isset($_SESSION['Auth'], $_SESSION['userdata']['id'])) {
    $user = getUser($_SESSION['userdata']['id']);
    if ($user) {
        $posts = filterPosts();
        $follow_suggestions = filterFollowSuggestion();
    }
}

$pagecount = count($_GET);

if ($user && (int) $user['ac_status'] === 1 && !$pagecount) {
    showPage('header', ['page_title' => 'Trang chủ']);
    showPage('navbar');
    showPage('wall');
} elseif ($user && (int) $user['ac_status'] === 0 && !$pagecount) {
    showPage('header', ['page_title' => 'Xác minh Email của bạn']);
    showPage('verify_email');
} elseif ($user && (int) $user['ac_status'] === 2 && !$pagecount) {
    showPage('header', ['page_title' => 'Bị chặn']);
    showPage('blocked');
} elseif ($user && isset($_GET['editprofile']) && (int) $user['ac_status'] === 1) {
    showPage('header', ['page_title' => 'Chỉnh sửa hồ sơ']);
    showPage('navbar');
    showPage('edit_profile');
} elseif ($user && isset($_GET['u']) && (int) $user['ac_status'] === 1) {
    $profile = getUserByUsername($_GET['u']);
    if (!$profile) {
        showPage('header', ['page_title' => 'Không tìm thấy người dùng']);
        showPage('navbar');
        showPage('user_not_found');
    } else {
        $profile_post = getPostById($profile['id']);
        $profile['followers'] = getFollowers($profile['id']);
        $profile['following'] = getFollowing($profile['id']);
        showPage('header', ['page_title' => $profile['first_name'] . ' ' . $profile['last_name']]);
        showPage('navbar');
        showPage('profile');
    }
} elseif (isset($_GET['signup'])) {
    showPage('header', ['page_title' => 'Handbook - Đăng ký']);
    showPage('signup');
} elseif (isset($_GET['login'])) {
    showPage('header', ['page_title' => 'Handbook - Đăng nhập']);
    showPage('login');
} elseif (isset($_GET['forgotpassword'])) {
    showPage('header', ['page_title' => 'Handbook - Quên mật khẩu']);
    showPage('forgot_password');
} else {
    if ($user && (int) $user['ac_status'] === 1) {
        showPage('header', ['page_title' => 'Trang chủ']);
        showPage('navbar');
        showPage('wall');
    } elseif ($user && (int) $user['ac_status'] === 0) {
        showPage('header', ['page_title' => 'Xác minh Email của bạn']);
        showPage('verify_email');
    } elseif ($user && (int) $user['ac_status'] === 2) {
        showPage('header', ['page_title' => 'Chặn']);
        showPage('blocked');
    } else {
        showPage('header', ['page_title' => 'Handbook - Đăng nhập']);
        showPage('login');
    }
}

showPage('footer');
unset($_SESSION['error'], $_SESSION['formdata']);
