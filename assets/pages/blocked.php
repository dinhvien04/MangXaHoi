<?php
global $user;
?>

<body>
    <div class="login">
        <div class="col-md-4 col-sm-12 bg-white border rounded p-4 shadow-sm">
            <form>
                <div class="d-flex justify-content-center">

                    <img class="mb-4" src="assets/images/handbook.png" alt="" height="45">
                </div>
                <h1 class="h5 mb-3 fw-normal">Hello, <?= $user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['email'] . ') ' ?>Tài khoản của bạn đã bị chặn bởi Admin</h1>




                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <a href="assets/php/actions.php?logout" class="btn btn-danger" type="submit">Đăng xuất</a>



                </div>

            </form>
        </div>
    </div>