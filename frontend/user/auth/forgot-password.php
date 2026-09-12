<?php
if (!empty($_SESSION['forgot_otp']) && empty($_SESSION['auth_temp'])) $action = 'verify_reset_code';
elseif (!empty($_SESSION['auth_temp'])) $action = 'change_password';
else $action = 'forgot_password';
?>
<div class="hb-auth-page">
    <section class="hb-auth-brand-panel">
        <div class="hb-auth-brand"><span class="hb-brand-mark">H</span><strong>Handbook Social</strong></div>
        <div class="hb-auth-brand-copy"><span class="hb-auth-kicker">ACCOUNT RECOVERY</span><h1>Lấy lại tài khoản.<br>Tiếp tục kết nối.</h1><p>Mã xác minh có thời hạn ngắn và được bảo vệ bởi giới hạn số lần thử ở backend.</p></div>
        <div class="hb-auth-security-card"><i class="bi bi-shield-lock-fill"></i><div><strong>Khôi phục an toàn</strong><span>Mã xác minh hết hạn sau 5 phút và không thể dùng lại.</span></div></div>
    </section>
    <section class="hb-auth-form-panel">
        <form class="hb-auth-card" method="post" action="?action=<?= e($action) ?>">
            <?= csrfField() ?>
            <span class="hb-auth-mobile-brand"><span class="hb-brand-mark">H</span> Handbook Social</span>
            <?php if ($action === 'forgot_password'): ?>
                <span class="hb-state-icon hb-state-icon-small"><i class="bi bi-key"></i></span>
                <h2>Quên mật khẩu?</h2><p class="hb-auth-subtitle">Nhập email để nhận mã xác minh.</p>
                <label class="hb-field"><span>Email</span><input type="email" name="email" placeholder="you@example.com" autocomplete="email" required><?= showError('email') ?></label>
                <button class="hb-primary-button hb-auth-submit" type="submit">Gửi mã xác minh</button>
                <a class="hb-back-link hb-auth-back" href="?login&newfp"><i class="bi bi-arrow-left"></i> Quay lại đăng nhập</a>
                <div class="hb-auth-security"><i class="bi bi-info-circle"></i><span>Vì bảo mật, giao diện không xác nhận email có tồn tại trong hệ thống hay không.</span></div>
            <?php elseif ($action === 'verify_reset_code'): ?>
                <span class="hb-state-icon hb-state-icon-small"><i class="bi bi-envelope-check"></i></span>
                <h2>Nhập mã xác minh</h2><p class="hb-auth-subtitle">Mã 6 chữ số có hiệu lực trong 5 phút.</p>
                <p class="hb-otp-copy">Nếu email <strong><?= e($_SESSION['forgot_otp']['email'] ?? '') ?></strong> có tài khoản, mã đã được gửi.</p>
                <label class="hb-field"><span>Mã xác minh</span><input class="hb-otp-input" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" name="code" placeholder="000000" autocomplete="one-time-code" required><?= showError('email_verify') ?></label>
                <div class="hb-otp-meta"><span>Nhập đủ 6 chữ số để tiếp tục.</span><span class="hb-otp-countdown" data-otp-expires="<?= (int) ($_SESSION['forgot_otp']['expires_at'] ?? 0) ?>">Còn 05:00</span></div>
                <button class="hb-primary-button hb-auth-submit" type="submit">Xác minh mã</button>
                <div class="hb-auth-switch"><span>Không nhận được mã?</span><a href="?forgotpassword&newfp">Gửi lại từ đầu</a></div>
            <?php else: ?>
                <span class="hb-state-icon hb-state-icon-small"><i class="bi bi-lock"></i></span>
                <h2>Tạo mật khẩu mới</h2><p class="hb-auth-subtitle">Chọn mật khẩu mạnh và dễ nhớ với bạn.</p>
                <label class="hb-field"><span>Mật khẩu mới</span><input type="password" name="password" minlength="8" placeholder="Ít nhất 8 ký tự" autocomplete="new-password" required><?= showError('password') ?></label>
                <label class="hb-field"><span>Nhập lại mật khẩu</span><input type="password" name="password_confirm" minlength="8" placeholder="Nhập lại mật khẩu mới" autocomplete="new-password" data-confirm-password required></label>
                <div class="hb-password-rules"><span><i class="bi bi-check-circle-fill"></i> Tối thiểu 8 ký tự</span><span><i class="bi bi-shield-check"></i> Không dùng mật khẩu quá dễ đoán</span><span><i class="bi bi-lightbulb"></i> Nên kết hợp chữ và số</span></div>
                <button class="hb-primary-button hb-auth-submit" type="submit">Đổi mật khẩu</button>
                <p class="hb-auth-footnote">Sau khi đổi mật khẩu, bạn sẽ quay lại màn hình đăng nhập.</p>
            <?php endif; ?>
        </form>
    </section>
</div>
