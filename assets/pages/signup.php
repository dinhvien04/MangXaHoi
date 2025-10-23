<div class="login">
    <div class="col-lg-4 col-md-8 col-sm-12 bg-white border rounded p-4 shadow-sm">
        <form method="post" action="assets/php/actions.php?signup">
            <div class="d-flex justify-content-center">

                <img class="mb-4" src="assets/images/handbook.png" alt="" height="45">
            </div>
            <h1 class="h5 mb-3 fw-normal">Tạo tài khoản mới</h1>
            <div class="d-flex">
                <div class="form-floating mt-1 col-6 ">
                    <input type="text" name="first_name" value="<?= showFormData('first_name') ?>" class="form-control rounded-0" placeholder="Username/email">
                    <label for="floatingInput">Họ</label>
                </div>
                <div class="form-floating mt-1 col-6">
                    <input type="text" name="last_name" value="<?= showFormData('last_name') ?>" class="form-control rounded-0" placeholder="Username/email">
                    <label for="floatingInput">Tên</label>
                </div>
            </div>
            <?= showError('first_name') ?>
            <?= showError('last_name') ?>

            <div class="d-flex gap-3 my-3">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="gender" id="exampleRadios1"
                        value="1" <?= isset($_SESSION['formdata']) ? '' : 'checked' ?><?= showFormData('gender') == 1 ? 'checked' : '' ?>>
                    <label class="form-check-label" for="exampleRadios1">
                        Nam
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="gender" id="exampleRadios3"
                        value="2" <?= showFormData('gender') == 2 ? 'checked' : '' ?>>
                    <label class="form-check-label" for="exampleRadios3">
                        Nữ
                    </label>
                </div>
                <!-- <div class="form-check">
                    <input class="form-check-input" type="radio" name="gender" id="exampleRadios2"
                        value="0" <?= showFormData('gender') == 0 ? 'checked' : '' ?>>
                    <label class="form-check-label" for="exampleRadios2">
                        Other
                    </label>
                </div> -->
            </div>


            <div class="form-floating mt-1">
                <input type="email" name="email" value="<?= showFormData('email') ?>" class="form-control rounded-0" placeholder="Username/email">
                <label for="floatingInput">Email</label>
            </div>
            <?= showError('email') ?>

            <div class="form-floating mt-1">
                <input type="text" name="username" value="<?= showFormData('username') ?>" class="form-control rounded-0" placeholder="Username/email">
                <label for="floatingInput">username</label>
            </div>
            <?= showError('username') ?>

            <div class="form-floating mt-1">
                <input type="password" name="password" class="form-control rounded-0" id="floatingPassword" placeholder="Mật khẩu">
                <label for="floatingPassword">Mật khẩu</label>
            </div>
            <?= showError('password') ?>

            <div class="d-flex gap-3 my-3" style="display: none;">
                <div class="form-check">
                    <input class="form-check-input" type="hidden" name="role" id="roleAdmin"
                        value="Admin" <?= showFormData('role') == 'Admin' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="roleAdmin">
                       
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="hidden" name="role" id="roleUser"
                        value="User" <?= isset($_SESSION['formdata']) ? (showFormData('role') == 'User' ? 'checked' : '') : 'checked' ?>>
                    <label class="form-check-label" for="roleUser">
                       
                    </label>
                </div>
            </div>
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <button class="btn btn-primary" type="submit">Đăng ký</button>
                <a href="?login" class="text-decoration-none">Bạn đã có tài khoản?</a>


            </div>

        </form>
    </div>
</div>