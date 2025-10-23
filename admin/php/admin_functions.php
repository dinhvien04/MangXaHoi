<?php
require_once($function_url ?? '../../assets/php/functions.php');

//Kiểm tra tài khoản Admin //LOGIN
function checkAdminUser($login_data)
{
    global $db;
    $email = $login_data['email'];
    $password = $login_data['password'];
    $role = $login_data['role'];
    
    // Thêm điều kiện kiểm tra role là 'Admin'
    $query = " SELECT * FROM users WHERE email='$email' AND password='$password' AND role='Admin' ";
    $run = mysqli_query($db, $query);

    // Lấy thông tin người dùng nếu tồn tại
    $data['user'] = mysqli_fetch_assoc($run) ?? array();

    // Kiểm tra nếu có người dùng và role là Admin
    if (count($data['user']) > 0) {
        $data['status'] = true;
        $data['user_id'] = $data['user']['id'];
        $data['role'] = $data['user']['role'];
    } else {
        $data['status'] = false;
    }

    return $data;
}


function getAdmin($user_id)//LOGIN
{
    global $db;
    $query = "SELECT * FROM users WHERE id=$user_id";
    $run = mysqli_query($db, $query);
    return mysqli_fetch_assoc($run);
}


// QUẢN LÝ NGƯỜI DÙNG (QLND)

function getUsersList($searchKeyword = '')//Quản lý người dùng
{
    // Kết nối cơ sở dữ liệu (giả sử bạn đã có kết nối $conn)
    global $db;

    // Truy vấn SQL cơ bản
    $sql = "SELECT * FROM users";

    // Nếu có từ khóa tìm kiếm, thêm điều kiện WHERE
    if (!empty($searchKeyword)) {
        $searchKeyword = mysqli_real_escape_string($db, $searchKeyword);
        $sql .= " WHERE first_name LIKE '%$searchKeyword%' OR last_name LIKE '%$searchKeyword%' OR username LIKE '%$searchKeyword%' OR email LIKE '%$searchKeyword%'";
    }

    // Thực thi truy vấn
    $result = mysqli_query($db, $sql);

    // Xử lý kết quả
    $users = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
    }
    return $users;
}


// Xâm nhập vào tài khoản người dùng
function loginUserByAdmin($email)//Quẩn lý người dùng
{
    global $db;

    $query = "SELECT * FROM users WHERE email='$email'";
    $run = mysqli_query($db, $query);
    $data['user'] = mysqli_fetch_assoc($run) ?? array();
    if (count($data['user']) > 0) {
        $data['status'] = true;
    } else {
        $data['status'] = false;
    }

    return $data;
}

function totalCommentsCount()//Quẩn lý người dùng
{
    global $db;
    $query = "SELECT count(*) as row FROM comments";
    $run = mysqli_query($db, $query);
    return mysqli_fetch_assoc($run)['row'];
}

function totalPostsCount()//Quẩn lý người dùng
{
    global $db;
    $query = "SELECT count(*) as row FROM posts";
    $run = mysqli_query($db, $query);
    return mysqli_fetch_assoc($run)['row'];
}

function totalUsersCount()//Quẩn lý người dùng
{
    global $db;
    $query = "SELECT count(*) as row FROM users";
    $run = mysqli_query($db, $query);
    return mysqli_fetch_assoc($run)['row'];
}

function totalLikesCount()//Quẩn lý người dùng
{
    global $db;
    $query = "SELECT count(*) as row FROM likes";
    $run = mysqli_query($db, $query);
    return mysqli_fetch_assoc($run)['row'];
}


function blockUserByAdmin($user_id)//Quẩn lý người dùng
{
    global $db;
    $query = "UPDATE users SET ac_status=2 WHERE id=$user_id";
    return mysqli_query($db, $query);
}
function unblockUserByAdmin($user_id)//Quẩn lý người dùng
{
    global $db;
    $query = "UPDATE users SET ac_status=1 WHERE id=$user_id";
    return mysqli_query($db, $query);
}

//CẬP NHẬT THÔNG TIN(CNTT)
function updateAdmin($data)//Cập nhật thông tin
{
    global $db;
    $user_id = $data['user_id'];
    $first_name = $data['first_name'];
    $last_name = $data['last_name'];
    $email = $data['email'];
    $password = $data['password'];
    $role = $data['role'];
    
    // Cập nhật thông tin người dùng bao gồm role
    $query = "UPDATE users 
              SET first_name='$first_name',
                  last_name='$last_name',
                  email='$email',
                  password='$password',
                  role='$role'
              WHERE id='$user_id'";

    // Thực hiện truy vấn
    $result = mysqli_query($db, $query);

    // Kiểm tra và trả về kết quả
    if ($result) {
        return true; // Cập nhật thành công
    } else {
        return false; // Cập nhật thất bại
    }
}



// QUẢN LÝ BÀI ĐĂNG (QLBD)
function getPosts($search = '', $report_status = '') //Quản lý bài đăng
{
    global $db;
    
    // Câu lệnh SQL với JOIN giữa bảng posts và users
    $query = "SELECT p.*, u.username FROM posts p
              JOIN users u ON p.user_id = u.id
              WHERE 1";

    // Thêm điều kiện tìm kiếm nếu có
    if (!empty($search)) {
        $query .= " AND u.username LIKE CONCAT('%', ?, '%')";
    }

    // Thêm điều kiện báo cáo
    if ($report_status == 'reported') {
        $query .= " AND p.is_reported = 1";
    } elseif ($report_status == 'not_reported') {
        $query .= " AND p.is_reported = 0";
    }

    $stmt = $db->prepare($query);

    // Ràng buộc giá trị tìm kiếm
    if (!empty($search)) {
        $stmt->bind_param('s', $search);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $posts = [];
    while ($post = $result->fetch_assoc()) {
        $posts[] = $post;
    }

    return $posts;
}



function getLikesCount($post_id) //Quản lý bài đăng 
{
    global $db;

    // Kiểm tra giá trị đầu vào
    if (empty($post_id)) {
        return 0; // Nếu không có post_id, trả về 0
    }

    // Truy vấn để đếm số lượng "like" theo post_id
    $query = "SELECT COUNT(*) AS like_count FROM likes WHERE post_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $post_id); // Bind post_id kiểu số nguyên
    $stmt->execute();

    // Lấy kết quả từ câu truy vấn
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row['like_count'] ?? 0; // Trả về số lượng like (hoặc 0 nếu không có dữ liệu)
}

function getCommentsCount($post_id) //Quản lý bài đăng 
{
    global $db;

    // Truy vấn để đếm số lượng bình luận theo post_id
    $query = "SELECT COUNT(*) AS comment_count FROM comments WHERE post_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $post_id); // Bind post_id kiểu số nguyên
    $stmt->execute();

    // Lấy kết quả từ câu truy vấn
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row['comment_count'] ?? 0; // Trả về số lượng bình luận (hoặc 0 nếu không có dữ liệu)
}
function getCommentsAdmin($post_id) //Quản lý bài đăng 
{
    global $db;

    // Kiểm tra giá trị đầu vào
    if (empty($post_id)) {
        return []; // Nếu không có post_id, trả về mảng rỗng
    }

    // Truy vấn để lấy tất cả comment cho bài đăng với post_id
    $query = "SELECT comments.id, comments.comment, comments.created_at, users.username
              FROM comments
              JOIN users ON comments.user_id = users.id
              WHERE comments.post_id = ?
              ORDER BY comments.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $post_id); // Bind post_id kiểu số nguyên
    $stmt->execute();

    // Lấy kết quả từ câu truy vấn
    $result = $stmt->get_result();
    
    // Lấy tất cả các comment dưới dạng mảng
    $comments = $result->fetch_all(MYSQLI_ASSOC);

    return $comments; // Trả về mảng chứa tất cả comment
}
