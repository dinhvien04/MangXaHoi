<?php

$action = (string) ($_GET['action'] ?? '');

if ($action === 'signup') {
    $response = validateSignupForm($_POST);
    if ($response['status'] && createUser($_POST)) {
        header('Location:./?login&newuser');
        exit();
    }
    if ($response['status']) {
        $response = ['status' => false, 'msg' => 'Không thể tạo tài khoản. Vui lòng thử lại.', 'field' => 'checkuser'];
    }
    $_SESSION['error'] = $response;
    $_SESSION['formdata'] = $_POST;
    header('Location:./?signup');
    exit();
}

if ($action === 'login') {
    $response = validateLoginForm($_POST);
    if ($response['status']) {
        session_regenerate_id(true);
        $_SESSION['Auth'] = true;
        $_SESSION['userdata'] = $response['user'];
        if ((int) $response['user']['ac_status'] === 0) {
            $_SESSION['code'] = $code = random_int(111111, 999999);
            sendCode($response['user']['email'], 'Xác minh email của bạn', $code);
        }
        header('Location:./');
        exit();
    }
    $_SESSION['error'] = $response;
    $_SESSION['formdata'] = ['username_email' => $_POST['username_email'] ?? ''];
    header('Location:./?login');
    exit();
}

if ($action === 'forgot_password') {
    $email = trim((string) ($_POST['email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !isEmailRegistered($email)) {
        $_SESSION['error'] = ['msg' => $email === '' ? 'Vui lòng nhập email của bạn!' : 'Email chưa được đăng ký.', 'field' => 'email'];
        header('Location:./?forgotpassword');
        exit();
    }
    $_SESSION['forgot_email'] = $email;
    $_SESSION['forgot_code'] = $code = random_int(111111, 999999);
    sendCode($email, 'Quên mật khẩu của bạn?', $code);
    header('Location:./?forgotpassword&resended');
    exit();
}

if ($action === 'verify_reset_code') {
    $userCode = trim((string) ($_POST['code'] ?? ''));
    $code = (string) ($_SESSION['forgot_code'] ?? '');
    if ($code !== '' && hash_equals($code, $userCode)) {
        $_SESSION['auth_temp'] = true;
        unset($_SESSION['forgot_code']);
        header('Location:./?forgotpassword');
        exit();
    }
    $_SESSION['error'] = ['msg' => $userCode === '' ? 'Nhập mã gồm 6 chữ số!' : 'Mã xác minh không chính xác!', 'field' => 'email_verify'];
    header('Location:./?forgotpassword');
    exit();
}

if ($action === 'change_password') {
    if (empty($_SESSION['auth_temp']) || empty($_SESSION['forgot_email'])) {
        http_response_code(403);
        exit('Phiên đặt lại mật khẩu không hợp lệ.');
    }
    $password = (string) ($_POST['password'] ?? '');
    if (strlen($password) < 6) {
        $_SESSION['error'] = ['msg' => 'Mật khẩu mới phải có ít nhất 6 ký tự.', 'field' => 'password'];
        header('Location:./?forgotpassword');
        exit();
    }
    resetPassword($_SESSION['forgot_email'], $password);
    unset($_SESSION['auth_temp'], $_SESSION['forgot_email'], $_SESSION['forgot_code']);
    header('Location:./?login&reseted');
    exit();
}

if ($action === 'logout') {
    session_unset();
    session_destroy();
    header('Location:./');
    exit();
}

requireUserAuth();

if ($action === 'resend_code') {
    $_SESSION['code'] = $code = random_int(111111, 999999);
    sendCode($_SESSION['userdata']['email'], 'Xác minh email của bạn', $code);
    header('Location:./?resended');
    exit();
}

if ($action === 'verify_email') {
    $userCode = trim((string) ($_POST['code'] ?? ''));
    $code = (string) ($_SESSION['code'] ?? '');
    if ($code !== '' && hash_equals($code, $userCode) && verifyEmail($_SESSION['userdata']['email'])) {
        $_SESSION['userdata']['ac_status'] = 1;
        unset($_SESSION['code']);
        header('Location:./');
        exit();
    }
    $_SESSION['error'] = ['msg' => $userCode === '' ? 'Nhập mã gồm 6 chữ số!' : 'Mã xác minh không chính xác!', 'field' => 'email_verify'];
    header('Location:./');
    exit();
}

if ($action === 'block_user') {
    $userId = (int) ($_GET['id'] ?? 0);
    $username = (string) ($_GET['username'] ?? '');
    if (!blockUser($userId)) {
        http_response_code(400);
        exit('Không thể chặn người dùng này.');
    }
    header('Location:./?u=' . rawurlencode($username));
    exit();
}

if ($action === 'delete_post') {
    if (!deletePost((int) ($_GET['id'] ?? 0))) {
        http_response_code(403);
        exit('Bạn không có quyền xóa bài đăng này.');
    }
    redirectBackOrHome('./');
}

if ($action === 'update_profile') {
    $response = validateUpdateForm($_POST, $_FILES['profile_pic'] ?? []);
    if ($response['status'] && updateProfile($_POST, $_FILES['profile_pic'] ?? [])) {
        header('Location:./?editprofile&success');
        exit();
    }
    if ($response['status']) {
        $response = ['status' => false, 'msg' => 'Không thể cập nhật hồ sơ.', 'field' => 'username'];
    }
    $_SESSION['error'] = $response;
    header('Location:./?editprofile');
    exit();
}

if ($action === 'delete_comment') {
    if (!deleteOwnComment((int) ($_GET['id'] ?? 0))) {
        http_response_code(403);
        exit('Bạn không có quyền xóa bình luận này.');
    }
    redirectBackOrHome('./');
}

if ($action === 'update_comment') {
    if (!updateOwnComment($_POST['comment_id'] ?? 0, $_POST['comment_text'] ?? '')) {
        http_response_code(403);
        exit('Bạn không có quyền chỉnh sửa bình luận này hoặc nội dung không hợp lệ.');
    }
    redirectBackOrHome('./');
}

if ($action === 'update_post') {
    if (!updateOwnPost($_POST['post_id'] ?? 0, $_POST['post_content'] ?? '')) {
        http_response_code(403);
        exit('Bạn không có quyền chỉnh sửa bài viết này.');
    }
    header('Location:./?new_post_added');
    exit();
}

if ($action === 'report_post') {
    $ok = reportPost($_POST['post_id'] ?? 0);
    echo $ok ? 'Bài viết đã được báo cáo.' : 'Không thể báo cáo bài viết.';
    exit();
}

if ($action === 'add_post') {
    $file = $_FILES['post_img'] ?? [];
    $response = validatePostImage($file);
    if ($response['status'] && createPost($_POST, $file)) {
        header('Location:./?new_post_added');
        exit();
    }
    if ($response['status']) {
        $response = ['status' => false, 'msg' => 'Không thể tạo bài đăng.', 'field' => 'post_img'];
    }
    $_SESSION['error'] = $response;
    header('Location:./');
    exit();
}

http_response_code(400);
echo 'Yêu cầu không hợp lệ.';
