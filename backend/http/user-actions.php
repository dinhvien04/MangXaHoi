<?php

$action = (string) ($_GET['action'] ?? '');
requirePostRequest();
requireCsrf();

if ($action === 'signup') {
    if (!consumeRateLimit('signup', clientIp(), 8, 3600)) {
        $_SESSION['error'] = ['status' => false, 'msg' => 'Bạn đã tạo quá nhiều yêu cầu đăng ký. Vui lòng thử lại sau.', 'field' => 'checkuser'];
        header('Location: ./?signup');
        exit();
    }
    $response = validateSignupForm($_POST);
    if ($response['status'] && createUser($_POST)) {
        unset($_SESSION['formdata']);
        header('Location: ./?login&newuser');
        exit();
    }
    if ($response['status']) {
        $response = ['status' => false, 'msg' => 'Không thể tạo tài khoản. Email hoặc username có thể vừa được sử dụng.', 'field' => 'checkuser'];
    }
    $_SESSION['error'] = $response;
    $_SESSION['formdata'] = [
        'first_name' => $_POST['first_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'username' => $_POST['username'] ?? '',
        'gender' => $_POST['gender'] ?? '',
    ];
    header('Location: ./?signup');
    exit();
}

if ($action === 'login') {
    $response = validateLoginForm($_POST);
    if ($response['status']) {
        session_regenerate_id(true);
        $_SESSION['Auth'] = true;
        $_SESSION['userdata'] = $response['user'];
        unset($_SESSION['email_otp']);

        if ((int) $response['user']['ac_status'] === 0) {
            $sent = sendOtpToSession('email_otp', $response['user']['email'], 'verify_email', 'Xác minh email của bạn', false);
            if (!$sent['status']) {
                $_SESSION['error'] = [
                    'field' => 'email_verify',
                    'msg' => $sent['reason'] === 'mail'
                        ? 'Hệ thống email tạm thời không khả dụng. Vui lòng thử gửi lại sau.'
                        : 'Bạn đã yêu cầu quá nhiều mã xác minh. Vui lòng thử lại sau.',
                ];
            }
        }
        header('Location: ./');
        exit();
    }
    $_SESSION['error'] = $response;
    $_SESSION['formdata'] = ['username_email' => $_POST['username_email'] ?? ''];
    header('Location: ./?login');
    exit();
}

if ($action === 'forgot_password') {
    $email = normalizeEmail($_POST['email'] ?? '');
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = ['msg' => 'Vui lòng nhập email hợp lệ.', 'field' => 'email'];
        header('Location: ./?forgotpassword');
        exit();
    }
    if (!consumeRateLimit('forgot_password', $email . '|' . clientIp(), 5, 900)) {
        $_SESSION['error'] = ['msg' => 'Bạn đã yêu cầu quá nhiều mã. Vui lòng thử lại sau.', 'field' => 'email'];
        header('Location: ./?forgotpassword');
        exit();
    }

    if (isEmailRegistered($email)) {
        $sent = sendOtpToSession('forgot_otp', $email, 'forgot_password', 'Quên mật khẩu của bạn?', false);
        if (!$sent['status']) {
            $_SESSION['error'] = ['msg' => 'Hệ thống email tạm thời không khả dụng hoặc đã đạt giới hạn gửi mã. Vui lòng thử lại sau.', 'field' => 'email'];
            header('Location: ./?forgotpassword');
            exit();
        }
    } else {
        // Keep the visible flow the same to avoid exposing whether an account exists.
        $_SESSION['forgot_otp'] = buildOtpState($email, random_int(100000, 999999), 'forgot_password');
    }

    header('Location: ./?forgotpassword&sent');
    exit();
}

if ($action === 'verify_reset_code') {
    if (empty($_SESSION['forgot_otp']) || !is_array($_SESSION['forgot_otp'])) {
        http_response_code(403);
        exit('Phiên đặt lại mật khẩu không hợp lệ.');
    }
    $state = &$_SESSION['forgot_otp'];
    $result = verifyOtpState($state, $_POST['code'] ?? '');
    if ($result['ok'] && isEmailRegistered($state['email'] ?? '')) {
        $_SESSION['auth_temp'] = [
            'email' => (string) $state['email'],
            'expires_at' => time() + 600,
        ];
        unset($_SESSION['forgot_otp']);
        header('Location: ./?forgotpassword');
        exit();
    }
    if ($result['ok']) {
        unset($_SESSION['forgot_otp']);
        $result = ['ok' => false, 'reason' => 'invalid'];
    }
    $_SESSION['error'] = ['msg' => otpErrorMessage($result['reason']), 'field' => 'email_verify'];
    header('Location: ./?forgotpassword');
    exit();
}

if ($action === 'change_password') {
    $temp = $_SESSION['auth_temp'] ?? null;
    if (!is_array($temp) || empty($temp['email']) || (int) ($temp['expires_at'] ?? 0) < time()) {
        unset($_SESSION['auth_temp']);
        http_response_code(403);
        exit('Phiên đặt lại mật khẩu đã hết hạn hoặc không hợp lệ.');
    }
    $password = (string) ($_POST['password'] ?? '');
    if (strlen($password) < 8) {
        $_SESSION['error'] = ['msg' => 'Mật khẩu mới phải có ít nhất 8 ký tự.', 'field' => 'password'];
        header('Location: ./?forgotpassword');
        exit();
    }
    if (!resetPassword($temp['email'], $password)) {
        $_SESSION['error'] = ['msg' => 'Không thể đổi mật khẩu. Vui lòng yêu cầu mã mới.', 'field' => 'password'];
        header('Location: ./?forgotpassword');
        exit();
    }
    unset($_SESSION['auth_temp'], $_SESSION['forgot_otp']);
    session_regenerate_id(true);
    header('Location: ./?login&reseted');
    exit();
}

if ($action === 'logout') {
    unset($_SESSION['Auth'], $_SESSION['userdata'], $_SESSION['email_otp'], $_SESSION['forgot_otp'], $_SESSION['auth_temp']);
    session_regenerate_id(true);
    header('Location: ./');
    exit();
}

if ($action === 'resend_code' || $action === 'verify_email') {
    $user = requireUserAuth(true);
    if ((int) $user['ac_status'] !== 0) {
        header('Location: ./');
        exit();
    }

    if ($action === 'resend_code') {
        $sent = sendOtpToSession('email_otp', $user['email'], 'verify_email', 'Xác minh email của bạn', true);
        if (!$sent['status']) {
            $messages = [
                'cooldown' => 'Vui lòng chờ ít nhất 60 giây trước khi gửi lại mã.',
                'rate_limit' => 'Bạn đã yêu cầu quá nhiều mã. Vui lòng thử lại sau.',
                'mail' => 'Hệ thống email tạm thời không khả dụng. Vui lòng thử lại sau.',
            ];
            $_SESSION['error'] = ['msg' => $messages[$sent['reason']] ?? 'Không thể gửi lại mã.', 'field' => 'email_verify'];
            header('Location: ./');
            exit();
        }
        header('Location: ./?resended');
        exit();
    }

    if (empty($_SESSION['email_otp']) || !is_array($_SESSION['email_otp'])) {
        $_SESSION['error'] = ['msg' => 'Mã xác minh đã hết hạn. Vui lòng gửi lại mã mới.', 'field' => 'email_verify'];
        header('Location: ./');
        exit();
    }
    $state = &$_SESSION['email_otp'];
    $result = verifyOtpState($state, $_POST['code'] ?? '');
    if ($result['ok'] && verifyEmail($user['email'])) {
        $_SESSION['userdata'] = getUser($user['id']);
        unset($_SESSION['email_otp']);
        session_regenerate_id(true);
        header('Location: ./');
        exit();
    }
    $_SESSION['error'] = ['msg' => otpErrorMessage($result['reason']), 'field' => 'email_verify'];
    header('Location: ./');
    exit();
}

$user = requireUserAuth(false);

if ($action === 'block_user') {
    $userId = (int) ($_POST['user_id'] ?? 0);
    $target = getActiveUser($userId);
    if (!$target || !blockUser($userId)) {
        http_response_code(400);
        exit('Không thể chặn người dùng này.');
    }
    header('Location: ./?u=' . rawurlencode($target['username']));
    exit();
}

if ($action === 'delete_post') {
    if (!deletePost((int) ($_POST['post_id'] ?? 0))) {
        http_response_code(403);
        exit('Bạn không có quyền xóa bài đăng này.');
    }
    redirectBackOrHome('./');
}

if ($action === 'update_profile') {
    $response = validateUpdateForm($_POST, $_FILES['profile_pic'] ?? []);
    if ($response['status'] && updateProfile($_POST, $_FILES['profile_pic'] ?? [])) {
        header('Location: ./?editprofile&success');
        exit();
    }
    if ($response['status']) {
        $response = ['status' => false, 'msg' => 'Không thể cập nhật hồ sơ.', 'field' => 'username'];
    }
    $_SESSION['error'] = $response;
    header('Location: ./?editprofile');
    exit();
}

if ($action === 'delete_comment') {
    if (!deleteOwnComment((int) ($_POST['comment_id'] ?? 0))) {
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
        exit('Bạn không có quyền chỉnh sửa bài viết này hoặc nội dung không hợp lệ.');
    }
    redirectBackOrHome('./');
}

if ($action === 'report_post') {
    $ok = reportPost($_POST['post_id'] ?? 0);
    jsonResponse([
        'status' => $ok,
        'message' => $ok ? 'Bài viết đã được gửi tới quản trị viên để xem xét.' : 'Không thể báo cáo bài viết này.',
    ], $ok ? 200 : 400);
}

if ($action === 'add_post') {
    $file = $_FILES['post_img'] ?? [];
    $response = validatePostImage($file);
    if ($response['status']) {
        $postText = trim((string) ($_POST['post_text'] ?? ''));
        $hasFile = !empty($response['has_file']);
        if ($postText === '' && !$hasFile) {
            $_SESSION['error'] = ['status' => false, 'msg' => 'Vui lòng nhập nội dung bài viết hoặc chọn ảnh.', 'field' => 'post_img'];
            header('Location: ./');
            exit();
        }
        if (createPost($_POST, $hasFile ? $file : null)) {
            header('Location: ./?new_post_added');
            exit();
        }
        $response = ['status' => false, 'msg' => 'Không thể tạo bài đăng.', 'field' => 'post_img'];
    }
    $_SESSION['error'] = $response;
    header('Location: ./');
    exit();
}

http_response_code(400);
echo 'Yêu cầu không hợp lệ.';
