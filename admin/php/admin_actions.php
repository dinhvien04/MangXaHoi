<?php
    require_once('admin_functions.php');
    require_once '../../assets/php/send_code.php';



    if (isset($_GET['login'])) {
        if (checkAdminUser($_POST)['status']) {
            $_SESSION['admin_auth'] = checkAdminUser($_POST)['user_id'];
            header('Location:../');
        } else {
            $_SESSION['error'] = [
                "field" => "useraccess",
                "msg" => "Incorrect email/password",
            ];
            header('Location:../');
        }
    }
    if (isset($_GET['logout'])) {
        session_destroy();
        header('Location:../');
    }
    // điều hướng sự kiện Edit profile
    if (isset($_GET['updateprofile'])) {
        if (updateAdmin($_POST)) {
            $_SESSION['error'] = [
                "field" => "adminprofile",
                "msg" => "Cập nhật thành công !",
            ];
            header('Location:../?edit_profile');
        } else {
            $_SESSION['error'] = [
                "field" => "adminprofile",
                "msg" => "something went wrong, try again later",
            ];
            header('Location:../?edit_profile');
        }
    }

    if (isset($_GET['userlogin']) && isset($_SESSION['admin_auth'])) {


        $response = loginUserByAdmin($_GET['userlogin']);


        if ($response['status']) {
            $_SESSION['Auth'] = true;
            $_SESSION['userdata'] = $response['user'];

            if ($response['user']['ac_status'] == 0) {
                $_SESSION['code'] = $code = rand(111111, 999999);
                sendCode($response['user']['email'], 'Verify Your Email', $code);
            }

            header("location:../../");
        }
    }

    // Xóa bài đăng
    if (isset($_GET['delete_post'])) {
        $post_id = $_GET['delete_post'];
        deletePost($post_id);
        header('Location: ../?manage');
    }


// Check if delete_comment is set
if (isset($_GET['delete_comment'])) {
    $comment_id = $_GET['delete_comment'];
    $post_id = $_GET['post_id'];

    // Delete the comment
    $sql = "DELETE FROM comments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$comment_id])) {
        header("Location: view_comments.php?post_id=" . $post_id . "&success=Comment deleted");
        exit;
    } else {
        header("Location: view_comments.php?post_id=" . $post_id . "&error=Failed to delete comment");
        exit;
    }
}
// Xử lý duyệt bài đăng khi nút "Duyệt" được bấm
if (isset($_GET['approve_post'])) {
    $post_id = intval($_GET['approve_post']); // Lấy ID bài đăng cần duyệt

    // Cập nhật trạng thái bài đăng thành đã duyệt
    $sql = "UPDATE posts SET is_approved = 1, is_reported = 0 WHERE id = ?";
    $stmt = $db->prepare($sql);

    if ($stmt->execute([$post_id])) {
        // Chuyển hướng về trang quản lý với thông báo thành công
        header("Location: ../?manage&success=Post approved successfully");
    } else {
        // Chuyển hướng về trang quản lý với thông báo lỗi
        header("Location: ../?manage&error=Failed to approve post");
    }
    exit();
}


?>
