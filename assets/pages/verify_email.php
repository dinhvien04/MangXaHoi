<?php
global $user;
?>
<div class="login">
    <div class="col-md-4 col-sm-11 bg-white border rounded p-4 shadow-sm">
        <form method="post" action="assets/php/actions.php?verify_email">
            <div class="d-flex justify-content-center">


            </div>
            <h1 class="h5 mb-3 fw-normal">Xác minh ID Email của bạn (<?=$user['email']?>)</h1>


            <p>Nhập mã 6 chữ số được gửi đến bạn</p>
            <div class="form-floating mt-1">

                <input type="text" name="code" class="form-control rounded-0" id="floatingPassword"
                    placeholder="Mật khẩu">
                <label for="floatingPassword">######</label>
            </div>
            <?php
if(isset($_GET['resended'])){
    ?>
            <p class="text-success">Mã xác minh đã được gửi lại!</p>

            <?php
}
                ?>
            <?=showError('email_verify')?>

            <div class="mt-3 d-flex justify-content-between align-items-center">
                <button class="btn btn-primary" type="submit">Xác minh Email</button>
                <a href="assets/php/actions.php?resend_code" class="text-decoration-none" type="submit">Gửi lại mã</a>





            </div>
            <br>
            <a href="assets/php/actions.php?logout" class="text-decoration-none mt-5"><i
                    class="bi bi-arrow-left-circle-fill"></i>
                Đăng xuất</a>
        </form>
    </div>
</div>