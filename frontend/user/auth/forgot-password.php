<?php
if (isset($_SESSION['forgot_code']) && empty($_SESSION['auth_temp'])) $action = 'verify_reset_code';
elseif (!empty($_SESSION['auth_temp'])) $action = 'change_password';
else $action = 'forgot_password';
?>
<div class="login"><div class="col-lg-4 col-md-8 col-sm-12 bg-white border rounded p-4 shadow-sm">
<form method="post" action="?action=<?= e($action) ?>"><h1 class="h5 mb-3 fw-normal">Quên mật khẩu?</h1>
<?php if ($action === 'forgot_password'): ?><div class="form-floating"><input type="email" name="email" class="form-control rounded-0" placeholder="Email"><label>Nhập email của bạn</label></div><?= showError('email') ?><br><button class="btn btn-primary">Gửi mã xác minh</button><?php endif; ?>
<?php if ($action === 'verify_reset_code'): ?><p>Nhập mã 6 chữ số được gửi đến <?= e($_SESSION['forgot_email'] ?? '') ?></p><div class="form-floating"><input type="text" name="code" class="form-control rounded-0" placeholder="######"><label>######</label></div><?= showError('email_verify') ?><br><button class="btn btn-primary">Xác minh mã</button><?php endif; ?>
<?php if ($action === 'change_password'): ?><p>Nhập mật khẩu mới cho <?= e($_SESSION['forgot_email'] ?? '') ?></p><div class="form-floating"><input type="password" name="password" class="form-control rounded-0" placeholder="Mật khẩu"><label>Mật khẩu mới</label></div><?= showError('password') ?><br><button class="btn btn-primary">Đổi mật khẩu</button><?php endif; ?>
<br><br><a href="?login" class="text-decoration-none"><i class="bi bi-arrow-left-circle-fill"></i> Quay lại</a></form></div></div>
