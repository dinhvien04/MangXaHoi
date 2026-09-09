<?php if ($user && (int) $user['ac_status'] === 1): ?>
<div class="modal fade" id="addpost" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Thêm bài đăng mới</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <img src="" style="display:none" id="post_img" class="w-100 rounded border mb-2" alt="Xem trước bài đăng">
            <form method="post" action="?action=add_post" enctype="multipart/form-data">
                <?= csrfField() ?>
                <input class="form-control mb-3" name="post_img" type="file" id="select_post_img" accept="image/jpeg,image/png" required>
                <textarea name="post_text" class="form-control mb-3" rows="2" maxlength="10000" placeholder="Nói gì đó đi"></textarea>
                <button type="submit" class="btn btn-primary">Đăng</button>
            </form>
        </div>
    </div></div>
</div>
<div class="offcanvas offcanvas-end" tabindex="-1" id="notification_sidebar">
    <div class="offcanvas-header"><h5 class="offcanvas-title">Thông báo</h5><button class="btn-close" data-bs-dismiss="offcanvas"></button></div>
    <div class="offcanvas-body">
        <?php $notifications = getNotifications(50, 0); foreach ($notifications as $not): ?>
            <div class="d-flex align-items-center border-bottom p-3">
                <img src="public/images/profile/<?= e($not['from_profile_pic']) ?>" height="40" width="40" class="rounded-circle border me-2" alt="">
                <div class="flex-grow-1"><a href="?u=<?= rawurlencode($not['from_username']) ?>" class="text-decoration-none text-dark fw-bold"><?= e($not['from_first_name'] . ' ' . $not['from_last_name']) ?></a><p class="mb-0 small text-muted"><?= e($not['message']) ?></p><time class="small text-muted timeago" datetime="<?= e($not['created_at']) ?>"></time></div>
                <?php if ((int) $not['read_status'] === 0): ?><span class="badge bg-primary">New</span><?php endif; ?>
            </div>
        <?php endforeach; if (empty($notifications)): ?><p class="text-center text-muted">Không có thông báo nào</p><?php endif; ?>
    </div>
</div>
<div class="offcanvas offcanvas-end" tabindex="-1" id="message_sidebar">
    <div class="offcanvas-header"><h5 class="offcanvas-title">Tin nhắn</h5><button class="btn-close" data-bs-dismiss="offcanvas"></button></div>
    <div class="offcanvas-body" id="chatlist"><p class="text-center text-muted">Đang tải tin nhắn...</p></div>
</div>
<div class="modal fade" id="chatbox" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header bg-primary text-white"><a href="#" id="cplink" class="text-decoration-none text-white"><h5 class="modal-title"><img src="public/images/profile/default_profile.jpg" id="chatter_pic" height="40" width="40" class="rounded-circle border me-2" alt=""><span id="chatter_name"></span> (@<span id="chatter_username">Đang tải...</span>)</h5></a></div>
        <div class="modal-body d-flex flex-column-reverse gap-2" id="user_chat"></div>
        <div class="modal-footer"><div id="msgsender" class="input-group"><input type="text" maxlength="2000" class="form-control" id="msginput" placeholder="Nói điều gì đó..."><button class="btn btn-primary" id="sendmsg" type="button"><i class="bi bi-send"></i></button></div><div id="blerror" class="text-danger" style="display:none">Không thể nhắn tin do trạng thái chặn.</div></div>
    </div></div>
</div>
<?php endif; ?>
<?php if ($user): ?><script>window.currentUserId=<?= (int) $user['id'] ?>;</script><?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="public/js/jquery-3.6.0.min.js"></script>
<script src="public/js/security.js"></script>
<script src="public/js/jquery.timeago.js"></script>
<script src="public/js/app.js"></script>
<script src="public/js/features/posts.js"></script>
<script src="public/js/features/follow.js"></script>
<script src="public/js/features/likes.js"></script>
<script src="public/js/features/comments.js"></script>
<script src="public/js/features/search.js"></script>
<script src="public/js/features/notifications.js"></script>
<script src="public/js/features/messages.js"></script>
</body>
</html>
