<div class="login"><div class="col-lg-4 col-md-8 col-sm-12 bg-white border rounded p-4 shadow-sm">
<form method="post" action="?action=signup">
    <div class="d-flex justify-content-center"><img class="mb-4" src="public/images/handbook.png" alt="Handbook" height="45"></div>
    <h1 class="h5 mb-3 fw-normal">Tạo tài khoản mới</h1>
    <div class="d-flex"><div class="form-floating mt-1 col-6"><input type="text" name="first_name" value="<?= e(showFormData('first_name')) ?>" class="form-control rounded-0" placeholder="Họ" required><label>Họ</label></div><div class="form-floating mt-1 col-6"><input type="text" name="last_name" value="<?= e(showFormData('last_name')) ?>" class="form-control rounded-0" placeholder="Tên" required><label>Tên</label></div></div>
    <?= showError('first_name') ?><?= showError('last_name') ?>
    <div class="d-flex gap-3 my-3"><label><input type="radio" name="gender" value="1" <?= showFormData('gender') === '2' ? '' : 'checked' ?>> Nam</label><label><input type="radio" name="gender" value="2" <?= showFormData('gender') === '2' ? 'checked' : '' ?>> Nữ</label><label><input type="radio" name="gender" value="3" <?= showFormData('gender') === '3' ? 'checked' : '' ?>> Khác</label></div>
    <div class="form-floating mt-1"><input type="email" name="email" value="<?= e(showFormData('email')) ?>" class="form-control rounded-0" placeholder="Email" required><label>Email</label></div><?= showError('email') ?>
    <div class="form-floating mt-1"><input type="text" name="username" value="<?= e(showFormData('username')) ?>" class="form-control rounded-0" placeholder="Username" required><label>Username</label></div><?= showError('username') ?>
    <div class="form-floating mt-1"><input type="password" name="password" class="form-control rounded-0" placeholder="Mật khẩu" minlength="6" required><label>Mật khẩu</label></div><?= showError('password') ?>
    <div class="mt-3 d-flex justify-content-between"><button class="btn btn-primary" type="submit">Đăng ký</button><a href="?login" class="text-decoration-none">Bạn đã có tài khoản?</a></div>
</form></div></div>
