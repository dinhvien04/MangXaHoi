<?php

$action = (string) ($_GET['action'] ?? '');
requirePostRequest();
requireCsrf();

$admin = requireAdminAuth();

if ($action === 'logout') {
    unset(
        $_SESSION['Auth'],
        $_SESSION['userdata'],
        $_SESSION['admin_auth'],
        $_SESSION['email_otp'],
        $_SESSION['forgot_otp'],
        $_SESSION['auth_temp']
    );
    session_regenerate_id(true);
    header('Location: ../');
    exit();
}
if ($action === 'update_profile') {
    $_SESSION['error'] = updateAdmin($_POST)
        ? ['field' => 'adminprofile', 'msg' => 'Cập nhật thành công!']
        : ['field' => 'adminprofile', 'msg' => 'Không thể cập nhật thông tin. Kiểm tra email, mật khẩu tối thiểu 8 ký tự và dữ liệu trùng lặp.'];
    header('Location: ./?edit_profile');
    exit();
}
if ($action === 'update_role') {
    $ok = updateUserRoleByAdmin($_POST['id'] ?? 0, $_POST['role'] ?? 'User');
    $_SESSION['admin_flash'] = $ok ? 'Cập nhật vai trò thành công.' : 'Không thể cập nhật vai trò.';
    header('Location: ./');
    exit();
}
if ($action === 'delete_user') {
    $ok = deleteUserByAdmin($_POST['id'] ?? 0);
    $_SESSION['admin_flash'] = $ok ? 'Đã xóa người dùng và dữ liệu liên quan.' : 'Không thể xóa người dùng.';
    header('Location: ./');
    exit();
}
if ($action === 'delete_post') {
    if (!deletePost((int) ($_POST['id'] ?? 0), true)) {
        $_SESSION['error'] = ['field' => 'managepost', 'msg' => 'Không thể xóa bài đăng.'];
    }
    header('Location: ./?manage');
    exit();
}
if ($action === 'delete_comment') {
    if (!deleteCommentByAdmin((int) ($_POST['id'] ?? 0))) {
        $_SESSION['error'] = ['field' => 'managepost', 'msg' => 'Không thể xóa bình luận.'];
    }
    header('Location: ./?manage');
    exit();
}
if ($action === 'approve_post') {
    $ok = approvePostByAdmin((int) ($_POST['id'] ?? 0));
    header('Location: ./?manage&' . ($ok ? 'success=1' : 'error=1'));
    exit();
}

http_response_code(400);
echo 'Yêu cầu không hợp lệ.';
