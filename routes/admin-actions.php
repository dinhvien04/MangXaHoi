<?php

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/assets/php/send_code.php';

if (isset($_GET['login'])) {
    $auth = checkAdminUser($_POST);
    if ($auth['status']) {
        session_regenerate_id(true);
        $_SESSION['admin_auth'] = $auth['user_id'];
        header('Location:../');
        exit();
    }

    $_SESSION['error'] = [
        'field' => 'useraccess',
        'msg' => 'Email hoặc mật khẩu quản trị không đúng',
    ];
    header('Location:../pages/login.php');
    exit();
}

requireAdminAuth();

if (isset($_GET['logout'])) {
    unset($_SESSION['admin_auth']);
    header('Location:../pages/login.php');
    exit();
}

if (isset($_GET['updateprofile'])) {
    $_SESSION['error'] = updateAdmin($_POST)
        ? ['field' => 'adminprofile', 'msg' => 'Cập nhật thành công!']
        : ['field' => 'adminprofile', 'msg' => 'Không thể cập nhật thông tin. Vui lòng kiểm tra dữ liệu và thử lại.'];
    header('Location:../?edit_profile');
    exit();
}

if (isset($_GET['userlogin'])) {
    $response = loginUserByAdmin($_GET['userlogin']);
    if (!$response['status']) {
        http_response_code(404);
        exit('Không tìm thấy người dùng.');
    }

    session_regenerate_id(true);
    $_SESSION['Auth'] = true;
    $_SESSION['userdata'] = $response['user'];

    if ((int) $response['user']['ac_status'] === 0) {
        $_SESSION['code'] = $code = random_int(111111, 999999);
        sendCode($response['user']['email'], 'Xác minh email của bạn', $code);
    }

    header('Location:../../');
    exit();
}

if (isset($_GET['delete_post'])) {
    if (!deletePost((int) $_GET['delete_post'])) {
        $_SESSION['error'] = ['field' => 'managepost', 'msg' => 'Không thể xóa bài đăng.'];
    }
    header('Location:../?manage');
    exit();
}

if (isset($_GET['delete_comment'])) {
    if (!deleteCommentByAdmin((int) $_GET['delete_comment'])) {
        $_SESSION['error'] = ['field' => 'managepost', 'msg' => 'Không thể xóa bình luận.'];
    }
    header('Location:../?manage');
    exit();
}

if (isset($_GET['approve_post'])) {
    $ok = approvePostByAdmin((int) $_GET['approve_post']);
    $status = $ok ? 'success=Post approved successfully' : 'error=Failed to approve post';
    header('Location:../?manage&' . $status);
    exit();
}

http_response_code(400);
echo 'Yêu cầu không hợp lệ.';
