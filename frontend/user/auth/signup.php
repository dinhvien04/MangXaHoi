<div class="hb-auth-page">
    <section class="hb-auth-brand-panel">
        <div class="hb-auth-brand"><span class="hb-brand-mark">H</span><strong>Handbook Social</strong></div>
        <div class="hb-auth-brand-copy"><span class="hb-auth-kicker">JOIN THE COMMUNITY</span><h1>Một tài khoản.<br>Nhiều kết nối.</h1><p>Tạo hồ sơ Handbook để đăng bài, theo dõi bạn bè và bắt đầu những cuộc trò chuyện mới.</p></div>
        <div class="hb-auth-social-preview"><div class="hb-preview-avatar">H</div><div><strong>Handbook Community</strong><small>Luôn chào đón thành viên mới</small></div><p>Chào mừng bạn 👋</p><span>Hãy tạo tài khoản và bắt đầu chia sẻ.</span></div>
    </section>
    <section class="hb-auth-form-panel">
        <form class="hb-auth-card hb-auth-card-tall" method="post" action="?action=signup">
            <?= csrfField() ?>
            <span class="hb-auth-mobile-brand"><span class="hb-brand-mark">H</span> Handbook Social</span>
            <h2>Tạo tài khoản mới</h2><p class="hb-auth-subtitle">Tham gia cộng đồng Handbook chỉ trong vài bước.</p>
            <div class="hb-form-grid hb-form-grid-auth"><label class="hb-field"><span>Họ</span><input type="text" name="first_name" maxlength="100" value="<?= e(showFormData('first_name')) ?>" placeholder="Nguyễn" required><?= showError('first_name') ?></label><label class="hb-field"><span>Tên</span><input type="text" name="last_name" maxlength="100" value="<?= e(showFormData('last_name')) ?>" placeholder="Đình Viễn" required><?= showError('last_name') ?></label></div>
            <fieldset class="hb-gender-field"><legend>Giới tính</legend><div><label><input type="radio" name="gender" value="1" <?= showFormData('gender') === '2' || showFormData('gender') === '3' ? '' : 'checked' ?>><span>Nam</span></label><label><input type="radio" name="gender" value="2" <?= showFormData('gender') === '2' ? 'checked' : '' ?>><span>Nữ</span></label><label><input type="radio" name="gender" value="3" <?= showFormData('gender') === '3' ? 'checked' : '' ?>><span>Khác</span></label></div></fieldset>
            <label class="hb-field"><span>Email</span><input type="email" name="email" maxlength="255" value="<?= e(showFormData('email')) ?>" placeholder="you@example.com" autocomplete="email" required><?= showError('email') ?></label>
            <label class="hb-field"><span>Username</span><input type="text" name="username" minlength="3" maxlength="30" pattern="[A-Za-z0-9._]+" value="<?= e(showFormData('username')) ?>" placeholder="dinhvien04" autocomplete="username" required><?= showError('username') ?></label>
            <label class="hb-field"><span>Mật khẩu</span><input type="password" name="password" minlength="8" placeholder="Ít nhất 8 ký tự" autocomplete="new-password" required><?= showError('password') ?></label>
            <button class="hb-primary-button hb-auth-submit" type="submit">Đăng ký</button>
            <div class="hb-auth-switch"><span>Đã có tài khoản?</span><a href="?login">Đăng nhập</a></div>
        </form>
    </section>
</div>
