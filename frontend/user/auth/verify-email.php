<div class="hb-auth-page">
    <section class="hb-auth-brand-panel">
        <div class="hb-auth-brand"><span class="hb-brand-mark">H</span><strong>Handbook Social</strong></div>
        <div class="hb-auth-brand-copy"><span class="hb-auth-kicker">ONE LAST STEP</span><h1>Xác minh email.<br>Mở khóa Handbook.</h1><p>Hoàn tất bước cuối để sử dụng feed, tương tác, tìm kiếm và nhắn tin.</p></div>
        <div class="hb-auth-security-card"><i class="bi bi-envelope-check-fill"></i><div><strong><?= e($user['email']) ?></strong><span>Mã xác minh có hiệu lực trong 5 phút.</span></div></div>
    </section>
    <section class="hb-auth-form-panel">
        <div class="hb-auth-card">
            <span class="hb-auth-mobile-brand"><span class="hb-brand-mark">H</span> Handbook Social</span>
            <span class="hb-state-icon hb-state-icon-small"><i class="bi bi-envelope-check"></i></span>
            <h2>Xác minh email</h2><p class="hb-auth-subtitle">Nhập mã 6 chữ số được gửi đến <strong><?= e($user['email']) ?></strong>.</p>
            <?php if (isset($_GET['resended'])): ?><div class="hb-alert hb-alert-success"><i class="bi bi-check-circle-fill"></i><span>Mã xác minh đã được gửi lại.</span></div><?php endif; ?>
            <form method="post" action="?action=verify_email">
                <?= csrfField() ?>
                <label class="hb-field"><span>Mã xác minh</span><input class="hb-otp-input" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" name="code" placeholder="000000" autocomplete="one-time-code" required><?= showError('email_verify') ?></label>
                <button class="hb-primary-button hb-auth-submit" type="submit">Xác minh Email</button>
            </form>
            <div class="hb-verify-actions"><form method="post" action="?action=resend_code"><?= csrfField() ?><button class="hb-link-button" type="submit">Gửi lại mã</button></form><form method="post" action="?action=logout"><?= csrfField() ?><button class="hb-link-button text-danger" type="submit">Đăng xuất</button></form></div>
            <div class="hb-auth-security"><i class="bi bi-check2-circle"></i><span>Xác minh xong sẽ mở khóa toàn bộ tính năng của tài khoản.</span></div>
        </div>
    </section>
</div>
