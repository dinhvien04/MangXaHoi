<?php
require_once 'admin_functions.php';
require_once '../../assets/php/send_code.php';

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

if (empty($_SESSION['admin_auth'])) {
    http_response_code(403);
    exit('Bạn không có quyền thực hiện thao tác này.');
}

if (isset($_GET['logout'])) {
    unset($_SESSION['admin_auth']);
    header('Location:../pages/login.php');
    exit();
}

if (isset($_GET['updateprofile'])) {
    if (updateAdmin($_POST)) {
        $_SESSION['error'] = [
            'field' => 'adminprofile',
            'msg' => 'Cập nhật thành công!',
        ];
    } else {
        $_SESSION['error'] = [
            'field' => 'adminprofile',
            'msg' => 'Không thể cập nhật thông tin. Vui lòng kiểm tra dữ liệu và thử lại.',
        ];
    }
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
    $postId = (int) $_GET['delete_post'];
    if (!deletePost($postId)) {
        $_SESSION['error'] = [
            'field' => 'managepost',
            'msg' => 'Không thể xóa bài đăng.',
        ];
    }
    header('Location:../?manage');
    exit();
}

if (isset($_GET['delete_comment'])) {
    $commentId = (int) $_GET['delete_comment'];
    if ($commentId <= 0) {
        http_response_code(400);
        exit('ID bình luận không hợp lệ.');
    }

    $stmt = $db->prepare('DELETE FROM comments WHERE id = ?');
    $stmt->bind_param('i', $commentId);
    $ok = $stmt->execute();
    $stmt->close();

    if (!$ok) {
        $_SESSION['error'] = [
            'field' => 'managepost',
            'msg' => 'Không thể xóa bình luận.',
        ];
    }
    header('Location:../?manage');
    exit();
}

if (isset($_GET['approve_post'])) {
    $postId = (int) $_GET['approve_post'];
    if ($postId <= 0) {
        http_response_code(400);
        exit('ID bài đăng không hợp lệ.');
    }

    $stmt = $db->prepare('UPDATE posts SET is_approved = 1, is_reported = 0 WHERE id = ?');
    $stmt->bind_param('i', $postId);
    $ok = $stmt->execute();
    $stmt->close();

    $status = $ok ? 'success=Post approved successfully' : 'error=Failed to approve post';
    header('Location:../?manage&' . $status);
    exit();
}

http_response_code(400);
echo 'Yêu cầu không hợp lệ.';
?>
