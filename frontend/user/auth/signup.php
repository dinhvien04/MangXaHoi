<div class="login"><div class="col-lg-4 col-md-8 col-sm-12 bg-white border rounded p-4 shadow-sm">
<form method="post" action="?action=signup">
    <?= csrfField() ?>
    <div class="d-flex justify-content-center"><img class="mb-4" src="public/images/handbook.png" alt="Handbook" height="45"></div>
    <h1 class="h5 mb-3 fw-normal">Tạo tài khoản mới</h1>
    <div class="d-flex"><div class="form-floating mt-1 col-6"><input type="text" name="first_name" maxlength="100" value="<?= e(showFormData('first_name')) ?>" class="form-control rounded-0" placeholder="Họ" required><label>Họ</label></div><div class="form-floating mt-1 col-6"><input type="text" name="last_name" maxlength="100" value="<?= e(showFormData('last_name')) ?>" class="form-control rounded-0" placeholder="Tên" required><label>Tên</label></div></div>
    <?= showError('first_name') ?><?= showError('last_name') ?>
    <div class="d-flex gap-3 my-3"><label><input type="radio" name="gender" value="1" <?= showFormData('gender') === '2' || showFormData('gender') === '3' ? '' : 'checked' ?>> Nam</label><label><input type="radio" name="gender" value="2" <?= showFormData('gender') === '2' ? 'checked' : '' ?>> Nữ</label><label><input type="radio" name="gender" value="3" <?= showFormData('gender') === '3' ? 'checked' : '' ?>> Khác</label></div>
    <div class="form-floating mt-1"><input type="email" name="email" maxlength="255" value="<?= e(showFormData('email')) ?>" class="form-control rounded-0" placeholder="Email" autocomplete="email" required><label>Email</label></div><?= showError('email') ?>
    <div class="form-floating mt-1"><input type="text" name="username" minlength="3" maxlength="30" pattern="[A-Za-z0-9._]+" value="<?= e(showFormData('username')) ?>" class="form-control rounded-0" placeholder="Username" autocomplete="username" required><label>Username</label></div><?= showError('username') ?>
    <div class="form-floating mt-1"><input type="password" name="password" class="form-control rounded-0" placeholder="Mật khẩu" minlength="8" autocomplete="new-password" required><label>Mật khẩu (ít nhất 8 ký tự)</label></div><?= showError('password') ?>
    <div class="mt-3 d-flex justify-content-between"><button class="btn btn-primary" type="submit">Đăng ký</button><a href="?login" class="text-decoration-none">Bạn đã có tài khoản?</a></div>
</form></div></div>
