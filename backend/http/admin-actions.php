<?php

$action = (string) ($_GET['action'] ?? '');

if ($action === 'login') {
    $auth = checkAdminUser($_POST);
    if ($auth['status']) {
        session_regenerate_id(true);
        $_SESSION['admin_auth'] = $auth['user_id'];
        header('Location:./');
        exit();
    }
    $_SESSION['error'] = ['field' => 'useraccess', 'msg' => 'Email hoặc mật khẩu quản trị không đúng'];
    header('Location:./');
    exit();
}

requireAdminAuth();

if ($action === 'logout') {
    unset($_SESSION['admin_auth']);
    header('Location:./');
    exit();
}
if ($action === 'update_profile') {
    $_SESSION['error'] = updateAdmin($_POST)
        ? ['field' => 'adminprofile', 'msg' => 'Cập nhật thành công!']
        : ['field' => 'adminprofile', 'msg' => 'Không thể cập nhật thông tin. Vui lòng kiểm tra dữ liệu và thử lại.'];
    header('Location:./?edit_profile');
    exit();
}
if ($action === 'user_login') {
    $response = loginUserByAdmin($_GET['email'] ?? '');
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
    header('Location:../');
    exit();
}
if ($action === 'update_role') {
    $ok = updateUserRoleByAdmin($_POST['id'] ?? 0, $_POST['role'] ?? 'User');
    $_SESSION['admin_flash'] = $ok ? 'Cập nhật vai trò thành công.' : 'Không thể cập nhật vai trò.';
    header('Location:./');
    exit();
}
if ($action === 'delete_user') {
    $ok = deleteUserByAdmin($_POST['id'] ?? 0);
    $_SESSION['admin_flash'] = $ok ? 'Đã xóa người dùng.' : 'Không thể xóa người dùng.';
    header('Location:./');
    exit();
}
if ($action === 'delete_post') {
    if (!deletePost((int) ($_GET['id'] ?? 0))) {
        $_SESSION['error'] = ['field' => 'managepost', 'msg' => 'Không thể xóa bài đăng.'];
    }
    header('Location:./?manage');
    exit();
}
if ($action === 'delete_comment') {
    if (!deleteCommentByAdmin((int) ($_GET['id'] ?? 0))) {
        $_SESSION['error'] = ['field' => 'managepost', 'msg' => 'Không thể xóa bình luận.'];
    }
    header('Location:./?manage');
    exit();
}
if ($action === 'approve_post') {
    $ok = approvePostByAdmin((int) ($_GET['id'] ?? 0));
    header('Location:./?manage&' . ($ok ? 'success=1' : 'error=1'));
    exit();
}

http_response_code(400);
echo 'Yêu cầu không hợp lệ.';
