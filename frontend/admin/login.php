<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
    <meta name="referrer" content="same-origin">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../public/admin/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../public/admin/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../public/css/handbook-desktop.css">
    <title>Handbook Admin | Đăng nhập</title>
</head>
<body class="hb-admin-login-body">
    <main class="hb-admin-login-page">
        <section class="hb-admin-login-hero">
            <div class="hb-admin-brand"><span class="hb-brand-mark">H</span><strong>Handbook Admin</strong></div>
            <div class="hb-admin-hero-copy"><span>CONTROL CENTER</span><h1>Quản trị Handbook nhanh, rõ, an toàn.</h1><p>Theo dõi người dùng, bài viết và trạng thái hệ thống trong một bảng điều khiển duy nhất.</p></div>
            <div class="hb-admin-security-card"><span>ADMIN SECURITY</span><strong><i class="fas fa-shield-alt"></i> CSRF protected</strong><strong><i class="fas fa-user-lock"></i> Session role revalidation</strong><strong><i class="fas fa-lock"></i> Protected admin actions</strong></div>
        </section>
        <section class="hb-admin-login-form-wrap">
            <form action="?action=login" method="post" class="hb-admin-login-form">
                <?= csrfField() ?>
                <a href="../" class="hb-back-link"><i class="fas fa-arrow-left"></i> Quay lại Handbook Social</a>
                <h2>Đăng nhập Admin</h2><p>Chỉ dành cho quản trị viên được cấp quyền.</p>
                <?= showError('useraccess') ?>
                <label class="hb-field"><span>Email</span><input type="email" name="email" placeholder="admin@example.com" autocomplete="username" required></label>
                <label class="hb-field"><span>Mật khẩu</span><input type="password" name="password" placeholder="••••••••" autocomplete="current-password" required></label>
                <button class="hb-primary-button hb-auth-submit" type="submit">Đăng nhập</button>
            </form>
        </section>
    </main>
    <script src="../public/admin/plugins/jquery/jquery.min.js"></script>
    <script src="../public/js/security.js"></script>
    <script src="../public/admin/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
