<?php
$function_url = "../assets/php/functions.php";
include('./php/admin_functions.php');

if (!isset($_SESSION['admin_auth'])) header('Location:./pages/login.php');
$admin = getAdmin($_SESSION['admin_auth']);

$message = ''; // Biến để lưu thông báo

if (isset($_POST['update_role']) && isset($_POST['id'])) {

    $role = $_POST['update_role'];
    $id = $_POST['id'];

    // Cập nhật vai trò người dùng
    $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $role, $id);
    $result = $stmt->execute();
    $stmt->close();

    // Kiểm tra kết quả và hiển thị thông báo
    if ($result) {
        $message = 'Cập nhật vai trò người dùng thành công!';
    } else {
        $message = 'Cập nhật vai trò người dùng thất bại. Vui lòng thử lại.';
    }
}

if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];

    // Thực hiện câu truy vấn xóa người dùng
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Người dùng đã được xóa thành công!</p>";
    } else {
        echo "<p style='color: red;'>Xóa người dùng thất bại!</p>";
    }

    $stmt->close();
}



?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="../assets/images/icon.png">
    <title>HandBook | Dashboard</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- JQVMap -->
    <link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
    <!-- summernote -->
    <link rel="stylesheet" href="plugins/summernote/summernote-bs4.min.css">



</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <!-- Load lại trang -->
        <!-- <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="../assets/images/icon.png" alt="AdminLTELogo" height="60" width="60">
        </div> -->

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>

            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Navbar Search -->
                <!-- <li class="nav-item">
        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
          <i class="fas fa-search"></i>
        </a>
        <div class="navbar-search-block">
          <form class="form-inline">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
              <div class="input-group-append">
                <button class="btn btn-navbar" type="submit">
                  <i class="fas fa-search"></i>
                </button>
                <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </form>
        </div>
      </li> -->

                <li class="nav-item">
                    <a class=" btn btn-sm btn-danger" href="php/admin_actions.php?logout" role="button">
                        Đăng xuất
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>

            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="index3.html" class="brand-link">
                <img src="../assets/images/icon.png" alt="AdminLTE Logo" class="brand-image " style="opacity: .8">
                <span class="brand-text font-weight-light">Handbook</span>
            </a>


            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">

                    <div class="info">
                        <a href="#" class="d-block"><?= $admin['first_name'] . ' ' . $admin['last_name'] ?></a>
                    </div>
                </div>




                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
                        <li class="nav-item">
                            <a href="?dashboard" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>
                                    Quản lý người dùng

                                </p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="?edit_profile" class="nav-link">
                                <i class="nav-icon fas fa-user"></i>
                                <p>
                                    Cập nhật thông tin
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="?manage" class="nav-link">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>Quản lý bài đăng</p>
                            </a>
                        </li>

                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">
                                <?php
                                if (isset($_GET['edit_profile'])) {
                                    echo "Cập nhật thông tin";
                                } elseif (isset($_GET['manage'])) {
                                    echo "Quản lý bài đăng";
                                } else {
                                    echo "Quản lý người dùng";
                                } ?>
                            </h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">

                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <!-- Small boxes (Stat box) -->
                    <?php if (isset($_GET['edit_profile'])) {
                    } elseif (isset($_GET['manage'])) {
                    } else {
                    ?>
                        <div class="row">
                            <div class="col-lg-3 col-6">
                                <!-- small box -->
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3><?= totalUsersCount() ?></h3>

                                        <p>Tổng người dùng</p>
                                    </div>
                                    <div class="icon">
                                        <i class="ion ion-bag"></i>
                                    </div>
                                </div>
                            </div>
                            <!-- ./col -->
                            <div class="col-lg-3 col-6">
                                <!-- small box -->
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h3><?= totalPostsCount() ?></h3>
                                        <p>Tổng baid đăng</p>
                                    </div>

                                    <div class="icon">
                                        <i class="ion ion-stats-bars"></i>
                                    </div>

                                </div>
                            </div>
                            <!-- ./col -->
                            <div class="col-lg-3 col-6">
                                <!-- small box -->
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h3><?= totalCommentsCount() ?></h3>
                                        <p>Tổng bình luận</p>
                                    </div>
                                    <div class="icon">
                                        <i class="ion ion-person-add"></i>
                                    </div>

                                </div>
                            </div>
                            <!-- ./col -->
                            <div class="col-lg-3 col-6">
                                <!-- small box -->
                                <div class="small-box bg-danger">
                                    <div class="inner">
                                        <h3><?= totalLikesCount() ?></h3>
                                        <p>Tổng lượt thích</p>
                                    </div>
                                    <div class="icon">
                                        <i class="ion ion-pie-graph"></i>
                                    </div>
                                </div>
                            </div>
                            <!-- ./col -->
                        </div>
                    <?php
                    }

                    ?>

                    <!-- /.row -->
                    <!-- Main row -->
                    <div class="row">
                        <?php
                        if (isset($_GET['edit_profile'])) {
                        ?>
                            <div class="card card-primary col-12">
                                <div class="card-header">
                                    <h3 class="card-title">Cập nhật thông tin</h3>
                                </div>
                                <!-- form start -->
                                <?= showError('adminprofile') ?>
                                <form method="post" action="php/admin_actions.php?updateprofile">
                                    <input type="hidden" name="user_id" value="<?= $admin['id'] ?>">
                                    <div class="card-body">
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label for="firstName">Họ</label>
                                                <input type="text" name="first_name" value="<?= $admin['first_name'] ?>"
                                                    class="form-control" id="firstName" placeholder="Enter First Name"
                                                    required>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="lastName">Tên</label>
                                                <input type="text" name="last_name" value="<?= $admin['last_name'] ?>"
                                                    class="form-control" id="lastName" placeholder="Enter Last Name"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleInputEmail1">Địa chỉ email</label>
                                            <input type="email" name="email" value="<?= $admin['email'] ?>"
                                                class="form-control" id="exampleInputEmail1" placeholder="Enter email"
                                                required>
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleInputPassword1">Mật khẩu</label>
                                            <input type="text" name="password" value="<?= $admin['password'] ?>"
                                                class="form-control" id="exampleInputPassword1" placeholder="Password">
                                        </div>

                                        <!-- Dropdown Role -->
                                        <div class="form-group">
                                            <!-- <label for="role">Chọn vai trò</label><br> -->
                                            <div class="form-check">
                                                <input class="form-check-input" type="hidden" name="role[]" id="role-admin"
                                                    value="Admin" <?= $admin['role'] == 'Admin' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="role-admin"></label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="hidden" name="role[]" id="role-user"
                                                    value="User" <?= $admin['role'] == 'User' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="role-user"></label>
                                            </div>
                                        </div>

                                    </div>
                                    <!-- /.card-body -->

                                    <div class="card-footer">
                                        <button type="submit" class="btn btn-primary">Cập nhật thông tin</button>
                                    </div>
                                </form>
                            </div>



                        <?php
                        } elseif (isset($_GET['manage'])) { ?>


                            <?php
                            // Kiểm tra xem có từ khóa tìm kiếm không từ POST request
                            $search = isset($_POST['search']) ? trim($_POST['search']) : '';

                            // Gọi hàm getPosts() để lấy danh sách bài đăng
                            $posts = getPosts($search);
                            ?>

                            <div class="card w-100">
                                <!-- Header của card -->
                                <div class="card-header d-flex justify-content-between ">
                                    <h3 class="card-title">Danh Sách Bài Đăng</h3>

                                    <!-- Form tìm kiếm -->
                                    <form method="POST" action="" class="d-flex">
                                        <input type="text" name="search" class="form-control mr-2"
                                            placeholder="Tìm kiếm bài đăng" value="<?= htmlspecialchars($search) ?>">
                                        <input type="submit" class="btn btn-primary" value="Tìm kiếm">
                                    </form>
                                </div>

                                <!-- Nội dung chính của card -->
                                <div class="card-body">
                                    <?php if (!empty($posts)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Tên người dùng</th>
                                                        <th>Hình Ảnh</th>
                                                        <th>Nội Dung</th>
                                                        <th>Comment</th>
                                                        <th>Ngày Tạo</th>
                                                        <th>Số lượng Like</th>
                                                        <th>Số Lượng Comment</th>
                                                        <th>Trạng Thái Báo Cáo</th>
                                                        <th>Hành Động</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($posts as $index => $post): ?>
                                                        <tr>
                                                            <td><?= $index + 1 ?></td>
                                                            <td><?= htmlspecialchars($post['username']) ?></td>
                                                            <td>
                                                            <img src="/assets/images/posts/<?= htmlspecialchars($post['post_img']) ?>" class="img-fluid" width="100" height="120" alt="Post Image">
                                                            </td>
                                                            <td><?= htmlspecialchars($post['post_text']) ?></td>
                                                            <td>
                                                                <?php
                                                                $comments = getCommentsAdmin($post['id']);
                                                                if (!empty($comments)):
                                                                    foreach ($comments as $key => $comment): ?>
                                                                        <p class="comment <?= $key >= 3 ? 'd-none' : '' ?>">
                                                                            <strong><?= htmlspecialchars($comment['username']) ?>:</strong>
                                                                            <?= htmlspecialchars($comment['comment']) ?>
                                                                            <em>Ngày tạo: <?= htmlspecialchars($comment['created_at']) ?></em>
                                                                        </p>
                                                                    <?php endforeach;
                                                                    if (count($comments) > 3): ?>
                                                                        <a href="javascript:void(0);" class="toggle-comments text-primary">Xem thêm</a>
                                                                    <?php endif;
                                                                else:
                                                                    echo "Không có comment nào.";
                                                                endif;
                                                                ?>
                                                            </td>
                                                            <td><?= htmlspecialchars($post['created_at']) ?></td>
                                                            <td><?= getLikesCount($post['id']) ?></td>
                                                            <td><?= getCommentsCount($post['id']) ?></td>
                                                            <td>
                                                                <?php if ($post['is_reported'] == 1): ?>
                                                                    <span class="badge <?= $post['is_approved'] == 1 ? 'badge-primary' : 'badge-danger' ?>">
                                                                        <?= $post['is_approved'] == 1 ? 'Đã duyệt' : 'Đã bị báo cáo' ?>
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="badge badge-success">Không bị báo cáo</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <a href="php/admin_actions.php?delete_post=<?= $post['id'] ?>"
                                                                    class="btn btn-danger btn-sm"
                                                                    onclick="return confirm('Bạn có chắc chắn muốn xóa bài đăng này không?');">Xóa</a>
                                                                <?php if ($post['is_reported'] == 1 && (empty($post['is_approved']) || $post['is_approved'] == 0)): ?>
                                                                    <a href="php/admin_actions.php?approve_post=<?= $post['id'] ?>"
                                                                        class="btn btn-success btn-sm"
                                                                        onclick="return confirm('Bạn có chắc chắn muốn duyệt bài đăng này không?');">Duyệt</a>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p>Không có bài đăng nào.</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- JavaScript xử lý "Xem thêm" -->
                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    const toggleLinks = document.querySelectorAll('.toggle-comments');

                                    toggleLinks.forEach(link => {
                                        link.addEventListener('click', function () {
                                            const comments = this.parentElement.querySelectorAll('.comment');
                                            const isCollapsed = Array.from(comments).some(comment => comment.classList.contains('d-none'));
                                            
                                            comments.forEach((comment, index) => {
                                                if (index >= 3) {
                                                    comment.classList.toggle('d-none');
                                                }
                                            });
                                            
                                            this.textContent = isCollapsed ? 'Thu gọn' : 'Xem thêm';
                                        });
                                    });
                                });
                            </script>

                        <?php } else {
                        ?>


                            <?php
                            include "./pages/user.php";
                            ?>

                            <!-- /.card-header -->



                    </div>


                <?php
                        }
                ?>
                </div>
                <!-- /.row (main row) -->
        </div><!-- /.container-fluid -->
        </section>

        <!-- /.content -->
    </div>


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- <script>
    let typingTimer; // Timer để đợi trước khi gửi form
    const doneTypingInterval = 700; // Thời gian chờ (300ms)
    const searchInput = document.getElementById('searchInput');

    // Bắt sự kiện nhập vào ô tìm kiếm
    searchInput.addEventListener('input', () => {
        clearTimeout(typingTimer); // Xóa timer cũ để tránh gửi form liên tục
        typingTimer = setTimeout(() => {
            document.getElementById('searchForm').submit(); // Gửi form khi người dùng dừng nhập
        }, doneTypingInterval);
    });
    </script> -->


    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
    </aside>
    <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="plugins/jquery/jquery.min.js"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="plugins/jquery-ui/jquery-ui.min.js"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous">
    </script>

    <!-- ChartJS -->
    <script src="plugins/chart.js/Chart.min.js"></script>
    <!-- Sparkline -->
    <script src="plugins/sparklines/sparkline.js"></script>
    <!-- JQVMap -->
    <script src="plugins/jqvmap/jquery.vmap.min.js"></script>
    <script src="plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
    <!-- jQuery Knob Chart -->
    <script src="plugins/jquery-knob/jquery.knob.min.js"></script>
    <!-- daterangepicker -->
    <script src="plugins/moment/moment.min.js"></script>
    <script src="plugins/daterangepicker/daterangepicker.js"></script>
    <!-- Tempusdominus Bootstrap 4 -->
    <script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
    <!-- Summernote -->
    <script src="plugins/summernote/summernote-bs4.min.js"></script>
    <!-- overlayScrollbars -->
    <script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
    <!-- AdminLTE App -->





    <script src="dist/js/adminlte.js"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="dist/js/demo.js"></script>
    <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
    <script src="dist/js/pages/dashboard.js"></script>
    <script src="js/actions.js?v=<?= time() ?>"></script>

</body>

</html>
<?php

if (isset($_SESSION['error'])) {
    unset($_SESSION['error']);
}
?>