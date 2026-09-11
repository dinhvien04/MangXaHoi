<div class="hb-auth-page">
    <section class="hb-auth-brand-panel">
        <div class="hb-auth-brand"><span class="hb-brand-mark">H</span><strong>Handbook Social</strong></div>
        <div class="hb-auth-brand-copy"><span class="hb-auth-kicker">SOCIAL CAMPUS</span><h1>Kết nối gần hơn.<br>Chia sẻ dễ hơn.</h1><p>Handbook Social là không gian để bạn đăng bài, theo dõi bạn bè, trò chuyện và cập nhật những khoảnh khắc quan trọng mỗi ngày.</p></div>
        <div class="hb-auth-social-preview"><div class="hb-preview-avatar">QC</div><div><strong>Trần Quỳnh Chi</strong><small>Vừa xong · Công khai</small></div><p>Hello Handbook!</p><span>Một nơi nhỏ để kết nối với nhau.</span></div>
    </section>
    <section class="hb-auth-form-panel">
        <form class="hb-auth-card" method="post" action="?action=login">
            <?= csrfField() ?>
            <span class="hb-auth-mobile-brand"><span class="hb-brand-mark">H</span> Handbook Social</span>
            <h2>Chào mừng trở lại</h2><p class="hb-auth-subtitle">Đăng nhập để tiếp tục với Handbook Social.</p>
            <label class="hb-field"><span>Username hoặc email</span><input type="text" name="username_email" value="<?= e(showFormData('username_email')) ?>" placeholder="vd: dinhvien04" autocomplete="username" required><?= showError('username_email') ?></label>
            <label class="hb-field"><span>Mật khẩu</span><input type="password" name="password" placeholder="••••••••" autocomplete="current-password" required><?= showError('password') ?></label>
            <?= showError('checkuser') ?>
            <div class="hb-auth-inline"><span></span><a href="?forgotpassword&newfp">Quên mật khẩu?</a></div>
            <button class="hb-primary-button hb-auth-submit" type="submit">Đăng nhập</button>
            <div class="hb-auth-switch"><span>Chưa có tài khoản?</span><a href="?signup">Tạo tài khoản mới</a></div>
            <div class="hb-auth-security"><i class="bi bi-shield-check"></i><span>Phiên đăng nhập được bảo vệ và kiểm tra quyền ở backend.</span></div>
        </form>
    </section>
</div>
