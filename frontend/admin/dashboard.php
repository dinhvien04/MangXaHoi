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
    <title>Handbook Admin</title>
</head>
<body class="hb-admin-body">
<?php
$isManage = isset($_GET['manage']);
$isEdit = isset($_GET['edit_profile']);
?>
<div class="hb-admin-shell">
    <aside class="hb-admin-sidebar">
        <a href="./" class="hb-admin-sidebar-brand"><span class="hb-brand-mark">H</span><strong>Handbook Admin</strong></a>
        <span class="hb-admin-label">CONTROL CENTER</span>
        <nav class="hb-admin-nav">
            <a class="<?= !$isManage && !$isEdit ? 'is-active' : '' ?>" href="./"><span><i class="fas fa-users"></i></span> Quản lý người dùng</a>
            <a class="<?= $isManage ? 'is-active' : '' ?>" href="?manage"><span><i class="fas fa-newspaper"></i></span> Quản lý bài đăng</a>
            <a class="<?= $isEdit ? 'is-active' : '' ?>" href="?edit_profile"><span><i class="fas fa-user-cog"></i></span> Cập nhật thông tin</a>
        </nav>
        <div class="hb-admin-sidebar-note"><small>Đăng nhập với quyền</small><strong>Administrator</strong></div>
        <div class="hb-admin-account"><span class="hb-admin-avatar">AD</span><span><strong><?= e($admin['first_name'].' '.$admin['last_name']) ?></strong><small><?= e($admin['email']) ?></small></span></div>
    </aside>

    <div class="hb-admin-main">
        <header class="hb-admin-topbar">
            <div><h1><?= $isManage ? 'Quản lý bài đăng' : ($isEdit ? 'Cập nhật thông tin' : 'Quản lý người dùng') ?></h1><p>Handbook Social / Admin</p></div>
            <form method="post" action="?action=logout"><?= csrfField() ?><button class="hb-secondary-button hb-admin-logout" type="submit"><i class="fas fa-sign-out-alt"></i> Đăng xuất</button></form>
        </header>
        <main class="hb-admin-content">
            <?php if (!empty($_SESSION['admin_flash'])): ?><div class="hb-alert hb-alert-info"><i class="fas fa-info-circle"></i><span><?= e($_SESSION['admin_flash']) ?></span></div><?php unset($_SESSION['admin_flash']); endif; ?>

            <?php if ($isEdit): ?>
                <section class="hb-admin-settings-card">
                    <div class="hb-admin-section-head"><div><h2>Thông tin quản trị viên</h2><p>Cập nhật tên, email hoặc mật khẩu quản trị.</p></div></div>
                    <?= showError('adminprofile') ?>
                    <form method="post" action="?action=update_profile">
                        <?= csrfField() ?>
                        <div class="hb-admin-profile-summary"><span class="hb-admin-avatar hb-admin-avatar-lg">AD</span><span><strong><?= e($admin['first_name'].' '.$admin['last_name']) ?></strong><small>Administrator</small></span></div>
                        <div class="hb-settings-divider"></div>
                        <div class="hb-form-grid"><label class="hb-field"><span>Họ</span><input name="first_name" maxlength="100" value="<?= e($admin['first_name']) ?>" required></label><label class="hb-field"><span>Tên</span><input name="last_name" maxlength="100" value="<?= e($admin['last_name']) ?>" required></label><label class="hb-field hb-field-wide"><span>Email</span><input type="email" name="email" maxlength="255" value="<?= e($admin['email']) ?>" required></label><label class="hb-field hb-field-wide"><span>Mật khẩu mới</span><input type="password" minlength="8" name="password" placeholder="Để trống nếu không đổi · tối thiểu 8 ký tự"></label></div>
                        <div class="hb-admin-security-note"><i class="fas fa-shield-alt"></i><span><strong>Quyền Admin được kiểm tra lại từ database trên mỗi thao tác nhạy cảm.</strong><small>Thay đổi mật khẩu sẽ áp dụng ở lần xác thực kế tiếp.</small></span></div>
                        <div class="hb-settings-actions"><button class="hb-primary-button" type="submit">Lưu thay đổi</button><a class="hb-secondary-button" href="./">Hủy</a></div>
                    </form>
                </section>

            <?php elseif ($isManage): $search = trim((string)($_POST['search'] ?? '')); $reportStatus = (string)($_POST['report_status'] ?? ''); $adminPosts = getPosts($search, $reportStatus, 100, 0); ?>
                <section class="hb-admin-section">
                    <div class="hb-admin-section-head"><div><h2>Kiểm duyệt bài đăng</h2><p>Ưu tiên xử lý các bài viết đã được người dùng báo cáo.</p></div></div>
                    <?= showError('managepost') ?>
                    <form method="post" class="hb-admin-filters"><label class="hb-admin-search"><i class="fas fa-search"></i><input name="search" value="<?= e($search) ?>" placeholder="Tìm theo nội dung hoặc username"></label><select name="report_status"><option value="">Tất cả bài viết</option><option value="reported" <?= $reportStatus==='reported' ? 'selected' : '' ?>>Đã báo cáo</option><option value="not_reported" <?= $reportStatus==='not_reported' ? 'selected' : '' ?>>Chưa báo cáo</option></select><button class="hb-primary-button" type="submit">Tìm kiếm</button></form>
                    <div class="hb-admin-list-card">
                        <?php foreach ($adminPosts as $post): ?>
                            <article class="hb-admin-post-row">
                                <div class="hb-admin-post-user"><span class="hb-admin-avatar-sm"><?= e(strtoupper(substr((string) $post['username'], 0, 2))) ?></span><span><strong>@<?= e($post['username']) ?></strong><small>Người đăng bài</small></span></div>
                                <img class="hb-admin-post-thumb" src="../public/images/posts/<?= e($post['post_img']) ?>" alt="Bài đăng">
                                <div class="hb-admin-post-copy"><p><?= e($post['post_text']) ?></p><small><?= getLikesCount($post['id']) ?> thích · <?= getCommentsCount($post['id']) ?> bình luận · <?= e($post['created_at']) ?></small><span class="hb-admin-status <?= !empty($post['is_reported']) ? 'is-danger' : 'is-neutral' ?>"><?= !empty($post['is_reported']) ? 'Đã báo cáo' : 'Bình thường' ?></span></div>
                                <div class="hb-admin-row-actions">
                                    <?php if (!empty($post['is_reported']) || empty($post['is_approved'])): ?><form method="post" action="?action=approve_post"><?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$post['id'] ?>"><button class="hb-admin-action is-success" type="submit"><i class="fas fa-check"></i> Duyệt</button></form><?php endif; ?>
                                    <form method="post" action="?action=delete_post" onsubmit="return confirm('Xóa bài viết này?')"><?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$post['id'] ?>"><button class="hb-admin-action is-danger" type="submit"><i class="fas fa-trash"></i> Xóa</button></form>
                                </div>
                            </article>
                            <?php $adminComments = getCommentsAdmin($post['id']); if ($adminComments): ?><details class="hb-admin-comments"><summary>Xem <?= count($adminComments) ?> bình luận</summary><?php foreach ($adminComments as $comment): ?><div><span><strong>@<?= e($comment['username']) ?></strong> <?= e($comment['comment']) ?></span><form method="post" action="?action=delete_comment"><?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$comment['id'] ?>"><button type="submit">Xóa</button></form></div><?php endforeach; ?></details><?php endif; ?>
                        <?php endforeach; if (!$adminPosts): ?><div class="hb-admin-empty"><i class="fas fa-newspaper"></i><strong>Không có bài viết phù hợp</strong></div><?php endif; ?>
                    </div>
                </section>

            <?php else: $searchUser = trim((string)($_POST['search_user'] ?? '')); $users = getUsersList($searchUser, 100, 0); ?>
                <section class="hb-admin-section">
                    <div class="hb-admin-section-head"><div><h2>Tổng quan hệ thống</h2><p>Theo dõi nhanh các chỉ số chính của Handbook Social.</p></div></div>
                    <div class="hb-admin-stats">
                        <div class="hb-stat-card"><span class="hb-stat-icon"><i class="fas fa-users"></i></span><div><strong><?= totalUsersCount() ?></strong><span>Tổng người dùng</span><small>Dữ liệu hiện tại</small></div></div>
                        <div class="hb-stat-card"><span class="hb-stat-icon"><i class="fas fa-newspaper"></i></span><div><strong><?= totalPostsCount() ?></strong><span>Tổng bài đăng</span><small>Dữ liệu hiện tại</small></div></div>
                        <div class="hb-stat-card"><span class="hb-stat-icon"><i class="fas fa-comments"></i></span><div><strong><?= totalCommentsCount() ?></strong><span>Tổng bình luận</span><small>Dữ liệu hiện tại</small></div></div>
                        <div class="hb-stat-card"><span class="hb-stat-icon"><i class="fas fa-thumbs-up"></i></span><div><strong><?= totalLikesCount() ?></strong><span>Tổng lượt thích</span><small>Dữ liệu hiện tại</small></div></div>
                    </div>
                    <div class="hb-admin-table-card">
                        <div class="hb-admin-table-head"><div><h2>Danh sách người dùng</h2><p>Quản lý trạng thái và vai trò tài khoản.</p></div><form method="post" class="hb-admin-user-search"><label><i class="fas fa-search"></i><input name="search_user" value="<?= e($searchUser) ?>" placeholder="Tên, username hoặc email"></label><button class="hb-primary-button" type="submit">Tìm</button></form></div>
                        <div class="hb-admin-table-wrap"><table class="hb-admin-table"><thead><tr><th>ID</th><th>Người dùng</th><th>Email</th><th>Trạng thái</th><th>Vai trò</th><th>Hành động</th></tr></thead><tbody>
                            <?php foreach ($users as $listed): ?><tr><td>#<?= (int)$listed['id'] ?></td><td><div class="hb-admin-user-cell"><span class="hb-admin-avatar-sm"><?= e(strtoupper(substr($listed['first_name'],0,1).substr($listed['last_name'],0,1))) ?></span><span><strong><?= e($listed['first_name'].' '.$listed['last_name']) ?></strong><small>@<?= e($listed['username']) ?></small></span></div></td><td><?= e($listed['email']) ?></td><td><?php if ((int)$listed['ac_status']===0): ?><button class="hb-admin-status is-warning verify_user_btn" data-user-id="<?= (int)$listed['id'] ?>" type="button">Chưa xác minh</button><?php elseif ((int)$listed['ac_status']===2): ?><span class="hb-admin-status is-danger">Đã chặn</span><?php else: ?><span class="hb-admin-status is-success">Hoạt động</span><?php endif; ?></td><td><?php if ((int)$listed['id'] !== (int)$admin['id']): ?><form method="post" action="?action=update_role"><?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$listed['id'] ?>"><select class="hb-role-select" name="role" onchange="this.form.submit()"><option value="User" <?= $listed['role']==='User'?'selected':'' ?>>User</option><option value="Admin" <?= $listed['role']==='Admin'?'selected':'' ?>>Admin</option></select></form><?php else: ?><span class="hb-admin-status is-info">Admin</span><?php endif; ?></td><td><div class="hb-admin-table-actions"><?php if ((int)$listed['id'] !== (int)$admin['id'] && $listed['role']==='User'): ?><button class="hb-admin-action is-danger block_user_btn" style="<?= (int)$listed['ac_status']===2?'display:none':'' ?>" data-user-id="<?= (int)$listed['id'] ?>" type="button">Chặn</button><button class="hb-admin-action is-success unblock_user_btn" style="<?= (int)$listed['ac_status']===2?'':'display:none' ?>" data-user-id="<?= (int)$listed['id'] ?>" type="button">Mở chặn</button><form method="post" action="?action=user_login"><?= csrfField() ?><input type="hidden" name="user_id" value="<?= (int)$listed['id'] ?>"><button class="hb-admin-action is-info" type="submit">Đăng nhập</button></form><form method="post" action="?action=delete_user" onsubmit="return confirm('Xóa người dùng này và toàn bộ dữ liệu liên quan?')"><?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$listed['id'] ?>"><button class="hb-admin-action is-outline-danger" type="submit">Xóa</button></form><?php endif; ?></div></td></tr><?php endforeach; ?>
                        </tbody></table></div>
                    </div>
                </section>
            <?php endif; ?>
        </main>
    </div>
</div>
<script src="../public/admin/plugins/jquery/jquery.min.js"></script>
<script src="../public/js/security.js"></script>
<script src="../public/admin/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../public/js/admin/actions.js"></script>
</body>
</html>
