<div class="hb-settings-page">
    <div class="hb-settings-header"><a href="?u=<?= rawurlencode($user['username']) ?>" class="hb-back-link"><i class="bi bi-arrow-left"></i> Quay lại hồ sơ</a><div><h1>Chỉnh sửa hồ sơ</h1><p>Cập nhật thông tin hiển thị trên Handbook Social.</p></div></div>
    <section class="hb-card hb-settings-card">
        <form method="post" action="?action=update_profile" enctype="multipart/form-data">
            <?= csrfField() ?>
            <?php if (isset($_GET['success'])): ?><div class="hb-alert hb-alert-success"><i class="bi bi-check-circle-fill"></i><span>Hồ sơ đã được cập nhật.</span></div><?php endif; ?>
            <div class="hb-avatar-editor">
                <img src="public/images/profile/<?= e($user['profile_pic']) ?>" alt="Ảnh đại diện">
                <div><label class="hb-secondary-button" for="profile_pic"><i class="bi bi-camera"></i> Đổi ảnh đại diện</label><input class="visually-hidden" id="profile_pic" type="file" name="profile_pic" accept="image/jpeg,image/png"><small>JPG hoặc PNG · tối đa 1 MB</small><?= showError('profile_pic') ?></div>
            </div>
            <div class="hb-settings-divider"></div>
            <div class="hb-form-grid">
                <label class="hb-field"><span>Họ</span><input type="text" maxlength="100" name="first_name" value="<?= e($user['first_name']) ?>" required><?= showError('first_name') ?></label>
                <label class="hb-field"><span>Tên</span><input type="text" maxlength="100" name="last_name" value="<?= e($user['last_name']) ?>" required><?= showError('last_name') ?></label>
                <label class="hb-field hb-field-wide"><span>Email</span><input type="email" value="<?= e($user['email']) ?>" disabled><small>Email không thể chỉnh sửa tại đây.</small></label>
                <label class="hb-field hb-field-wide"><span>Username</span><input type="text" minlength="3" maxlength="30" pattern="[A-Za-z0-9._]+" value="<?= e($user['username']) ?>" name="username" required><?= showError('username') ?></label>
                <label class="hb-field hb-field-wide"><span>Mật khẩu mới</span><input type="password" minlength="8" name="password" placeholder="Để trống nếu không đổi" autocomplete="new-password"><small>Tối thiểu 8 ký tự.</small><?= showError('password') ?></label>
            </div>
            <div class="hb-settings-divider"></div>
            <div class="hb-settings-actions"><button class="hb-primary-button" type="submit">Lưu thay đổi</button><a class="hb-secondary-button" href="?u=<?= rawurlencode($user['username']) ?>">Hủy</a></div>
        </form>
    </section>
</div>
