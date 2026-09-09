<?php

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/assets/php/send_code.php';

if (isset($_GET['signup'])) {
    $response = validateSignupForm($_POST);
    if ($response['status'] && createUser($_POST)) {
        header('Location:../..?login&newuser');
        exit();
    }

    if ($response['status']) {
        $response = ['status' => false, 'msg' => 'Không thể tạo tài khoản. Vui lòng thử lại.', 'field' => 'checkuser'];
    }
    $_SESSION['error'] = $response;
    $_SESSION['formdata'] = $_POST;
    header('Location:../..?signup');
    exit();
}

if (isset($_GET['login'])) {
    $response = validateLoginForm($_POST);
    if ($response['status']) {
        session_regenerate_id(true);
        $_SESSION['Auth'] = true;
        $_SESSION['userdata'] = $response['user'];

        if ((int) $response['user']['ac_status'] === 0) {
            $_SESSION['code'] = $code = random_int(111111, 999999);
            sendCode($response['user']['email'], 'Xác minh email của bạn', $code);
        }
        header('Location:../..');
        exit();
    }

    $_SESSION['error'] = $response;
    $_SESSION['formdata'] = ['username_email' => $_POST['username_email'] ?? ''];
    header('Location:../..?login');
    exit();
}

if (isset($_GET['forgotpassword'])) {
    $email = trim((string) ($_POST['email'] ?? ''));
    if ($email === '') {
        $_SESSION['error'] = ['msg' => 'Vui lòng nhập email của bạn!', 'field' => 'email'];
        header('Location:../..?forgotpassword');
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !isEmailRegistered($email)) {
        $_SESSION['error'] = ['msg' => 'Email chưa được đăng ký.', 'field' => 'email'];
        header('Location:../..?forgotpassword');
        exit();
    }

    $_SESSION['forgot_email'] = $email;
    $_SESSION['forgot_code'] = $code = random_int(111111, 999999);
    sendCode($email, 'Quên mật khẩu của bạn?', $code);
    header('Location:../..?forgotpassword&resended');
    exit();
}

if (isset($_GET['verifycode'])) {
    $userCode = trim((string) ($_POST['code'] ?? ''));
    $code = (string) ($_SESSION['forgot_code'] ?? '');
    if ($code !== '' && hash_equals($code, $userCode)) {
        $_SESSION['auth_temp'] = true;
        unset($_SESSION['forgot_code']);
        header('Location:../..?forgotpassword');
        exit();
    }

    $_SESSION['error'] = [
        'msg' => $userCode === '' ? 'Nhập mã gồm 6 chữ số!' : 'Mã xác minh không chính xác!',
        'field' => 'email_verify',
    ];
    header('Location:../..?forgotpassword');
    exit();
}

if (isset($_GET['changepassword'])) {
    if (empty($_SESSION['auth_temp']) || empty($_SESSION['forgot_email'])) {
        http_response_code(403);
        exit('Phiên đặt lại mật khẩu không hợp lệ.');
    }

    $password = (string) ($_POST['password'] ?? '');
    if (strlen($password) < 6) {
        $_SESSION['error'] = ['msg' => 'Mật khẩu mới phải có ít nhất 6 ký tự.', 'field' => 'password'];
        header('Location:../..?forgotpassword');
        exit();
    }

    resetPassword($_SESSION['forgot_email'], $password);
    unset($_SESSION['auth_temp'], $_SESSION['forgot_email'], $_SESSION['forgot_code']);
    header('Location:../..?login&reseted');
    exit();
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location:../..');
    exit();
}

requireUserAuth();

if (isset($_GET['resend_code'])) {
    $_SESSION['code'] = $code = random_int(111111, 999999);
    sendCode($_SESSION['userdata']['email'], 'Xác minh email của bạn', $code);
    header('Location:../..?resended');
    exit();
}

if (isset($_GET['verify_email'])) {
    $userCode = trim((string) ($_POST['code'] ?? ''));
    $code = (string) ($_SESSION['code'] ?? '');
    if ($code !== '' && hash_equals($code, $userCode) && verifyEmail($_SESSION['userdata']['email'])) {
        $_SESSION['userdata']['ac_status'] = 1;
        unset($_SESSION['code']);
        header('Location:../..');
        exit();
    }

    $_SESSION['error'] = [
        'msg' => $userCode === '' ? 'Nhập mã gồm 6 chữ số!' : 'Mã xác minh không chính xác!',
        'field' => 'email_verify',
    ];
    header('Location:../..');
    exit();
}

if (isset($_GET['block'])) {
    $userId = (int) $_GET['block'];
    $username = (string) ($_GET['username'] ?? '');
    if (!blockUser($userId)) {
        http_response_code(400);
        exit('Không thể chặn người dùng này.');
    }
    header('Location:../..?u=' . rawurlencode($username));
    exit();
}

if (isset($_GET['deletepost'])) {
    if (!deletePost((int) $_GET['deletepost'])) {
        http_response_code(403);
        exit('Bạn không có quyền xóa bài đăng này.');
    }
    redirectBackOrHome('../..');
}

if (isset($_GET['updateprofile'])) {
    $response = validateUpdateForm($_POST, $_FILES['profile_pic'] ?? []);
    if ($response['status'] && updateProfile($_POST, $_FILES['profile_pic'] ?? [])) {
        header('Location:../..?editprofile&success');
        exit();
    }

    if ($response['status']) {
        $response = ['status' => false, 'msg' => 'Không thể cập nhật hồ sơ.', 'field' => 'username'];
    }
    $_SESSION['error'] = $response;
    header('Location:../..?editprofile');
    exit();
}

if (isset($_GET['deletecomment'])) {
    if (!deleteOwnComment((int) $_GET['deletecomment'])) {
        http_response_code(403);
        exit('Bạn không có quyền xóa bình luận này.');
    }
    redirectBackOrHome('../..');
}

if (isset($_GET['updatecomment']) && (string) $_GET['updatecomment'] === '1') {
    if (!updateOwnComment($_POST['comment_id'] ?? 0, $_POST['comment_text'] ?? '')) {
        http_response_code(403);
        exit('Bạn không có quyền chỉnh sửa bình luận này hoặc nội dung không hợp lệ.');
    }
    redirectBackOrHome('../..');
}

if (isset($_POST['update_post']) && (string) $_POST['update_post'] === '1') {
    $postId = (int) ($_POST['post_id'] ?? 0);
    $postContent = trim((string) ($_POST['post_content'] ?? ''));
    $currentUserId = (int) $_SESSION['userdata']['id'];
    if ($postId <= 0) {
        http_response_code(400);
        exit('ID bài viết không hợp lệ.');
    }

    $stmt = $db->prepare('UPDATE posts SET post_text = ? WHERE id = ? AND user_id = ?');
    $stmt->bind_param('sii', $postContent, $postId, $currentUserId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected < 1) {
        http_response_code(403);
        exit('Bạn không có quyền chỉnh sửa bài viết này.');
    }
    header('Location:../..?new_post_added');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'report_post') {
    $postId = (int) ($_POST['post_id'] ?? 0);
    if ($postId <= 0) {
        http_response_code(400);
        exit('ID bài viết không hợp lệ.');
    }

    $stmt = $db->prepare('UPDATE posts SET is_reported = 1, is_approved = 0 WHERE id = ?');
    $stmt->bind_param('i', $postId);
    $ok = $stmt->execute();
    $stmt->close();
    echo $ok ? 'Bài viết đã được báo cáo.' : 'Không thể báo cáo bài viết.';
    exit();
}

if (isset($_GET['addpost'])) {
    $file = $_FILES['post_img'] ?? [];
    $response = validatePostImage($file);
    if ($response['status'] && createPost($_POST, $file)) {
        header('Location:../..?new_post_added');
        exit();
    }

    if ($response['status']) {
        $response = ['status' => false, 'msg' => 'Không thể tạo bài đăng.', 'field' => 'post_img'];
    }
    $_SESSION['error'] = $response;
    header('Location:../..');
    exit();
}

http_response_code(400);
echo 'Yêu cầu không hợp lệ.';
