<?php

?>
<div class="card w-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <!-- Tiêu đề User Lists -->
        <h3 class="card-title mb-0">Danh sách người dùng</h3>

        <!-- Form tìm kiếm -->
        <form method="get" action="" id="searchForm">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Tìm kiếm người dùng"
                    value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" id="searchInput">
                <button type="submit" class="btn btn-primary">Tìm kiếm</button>
            </div>
        </form>
    </div>
</div>

<!-- /.card-header -->
<div class="card-body">
    <?php
    // Kiểm tra từ khóa tìm kiếm
    $searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';

    // Gọi hàm lấy danh sách người dùng với điều kiện tìm kiếm (nếu có)
    $userslist = getUsersList($searchKeyword);
    $count = 1;
    ?>
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>#No</th>
                <th>Người dùng</th>
                <th>Hành động</th>
                <th>Vai trò</th>
            </tr>
        </thead>
        <tbody>
            <!-- Liệt kê danh sách người dùng -->
            <?php
            if (!empty($userslist)) {
                foreach ($userslist as $user) {
            ?>
                    <tr>
                        <td>#<?= $count ?></td>
                        <td>
                            <div class="d-flex">
                                <div>
                                    <img src="../assets/images/profile/<?= $user['profile_pic'] ?>"
                                        class="rounded-circle border border-2 shadow-sm mx-2" width="55px" height="55px" />
                                </div>
                                <div>
                                    <h5><?= $user['first_name'] . ' ' . $user['last_name'] ?>
                                        -
                                        <span class="text-muted">@<?= $user['username'] ?></span>
                                    </h5>
                                    <h6 class="text-muted"><?= $user['email'] ?></h6>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2 px-2">
                                <!-- Login User -->
                                <a href="./php/admin_actions.php?userlogin=<?= $user['email'] ?>" target="_blank">
                                    <input type="button" value="Login User" class="btn btn-success btn-sm"
                                        style="margin-right: 10px;">
                                </a>

                                <!-- Verify User -->
                                <?php if ($user['ac_status'] == 0): ?>
                                    <input type="button" value="Verify" class="btn btn-warning btn-sm verify_user_btn"
                                        data-user-id="<?= $user['id'] ?>" style="margin-right: 10px;">
                                <?php endif; ?>

                                <!-- Block User -->
                                <input type="button" value="Block"
                                    style="display:<?= $user['ac_status'] == 1 ? '' : 'none' ?>; margin-right: 10px;"
                                    class="btn btn-danger btn-sm block_user_btn ub" data-user-id="<?= $user['id'] ?>">

                                <!-- Unblock User -->
                                <input type="button" value="Unblock"
                                    style="display:<?= $user['ac_status'] == 2 ? '' : 'none' ?>; margin-right: 10px;"
                                    class="btn btn-primary btn-sm unblock_user_btn" data-user-id="<?= $user['id'] ?>">

                                <!-- Delete User Form -->
                                <form method="POST" action="" class="m-0 p-0" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" name="delete_user" class="btn btn-danger btn-sm">Xóa</button>
                                </form>
                            </div>
                        </td>
                        <td>
                            <form method="post" action="">
                                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                <select name="update_role" onchange="this.form.submit()">
                                    <option value="Admin" <?= ($user['role'] == 'Admin' ? 'selected' : '') ?>>
                                        Admin
                                    </option>
                                    <option value="User" <?= ($user['role'] == 'User' ? 'selected' : '') ?>>
                                        Người dùng</option>
                                </select>
                            </form>
                        </td>
                    </tr>
            <?php
                    $count++;
                }
            } else {
                echo '<tr><td colspan="4">Không tìm thấy người dùng nào.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>