<?php
if (!empty($_SESSION['forgot_otp']) && empty($_SESSION['auth_temp'])) $action = 'verify_reset_code';
elseif (!empty($_SESSION['auth_temp'])) $action = 'change_password';
else $action = 'forgot_password';
?>
<div class="login"><div class="col-lg-4 col-md-8 col-sm-12 bg-white border rounded p-4 shadow-sm">
<form method="post" action="?action=<?= e($action) ?>"><?= csrfField() ?><h1 class="h5 mb-3 fw-normal">Quên mật khẩu?</h1>
<?php if ($action === 'forgot_password'): ?><div class="form-floating"><input type="email" name="email" class="form-control rounded-0" placeholder="Email" autocomplete="email" required><label>Nhập email của bạn</label></div><?= showError('email') ?><br><button class="btn btn-primary">Gửi mã xác minh</button><?php endif; ?>
<?php if ($action === 'verify_reset_code'): ?><p>Nếu email <strong><?= e($_SESSION['forgot_otp']['email'] ?? '') ?></strong> có tài khoản, mã 6 chữ số đã được gửi. Mã hết hạn sau 5 phút.</p><div class="form-floating"><input type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" name="code" class="form-control rounded-0" placeholder="######" required><label>######</label></div><?= showError('email_verify') ?><br><button class="btn btn-primary">Xác minh mã</button><?php endif; ?>
<?php if ($action === 'change_password'): ?><p>Nhập mật khẩu mới.</p><div class="form-floating"><input type="password" name="password" minlength="8" class="form-control rounded-0" placeholder="Mật khẩu" autocomplete="new-password" required><label>Mật khẩu mới (ít nhất 8 ký tự)</label></div><?= showError('password') ?><br><button class="btn btn-primary">Đổi mật khẩu</button><?php endif; ?>
<br><br><a href="?login&newfp" class="text-decoration-none"><i class="bi bi-arrow-left-circle-fill"></i> Quay lại</a></form></div></div>
