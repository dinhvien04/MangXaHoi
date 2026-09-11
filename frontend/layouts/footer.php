<?php if ($user && (int) $user['ac_status'] === 1): ?>
<div class="modal fade hb-modal hb-create-post-modal" id="addpost" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div><h5 class="modal-title">Tạo bài viết</h5><small>Đăng ảnh và chia sẻ nội dung mới.</small></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <form method="post" action="?action=add_post" enctype="multipart/form-data">
                <?= csrfField() ?>
                <div class="modal-body">
                    <div class="hb-modal-user">
                        <img class="hb-avatar hb-avatar-lg" src="public/images/profile/<?= e($user['profile_pic']) ?>" alt="">
                        <span><strong><?= e($user['first_name'] . ' ' . $user['last_name']) ?></strong><small><i class="bi bi-globe2"></i> Công khai</small></span>
                    </div>
                    <textarea name="post_text" class="hb-create-post-textarea" rows="4" maxlength="10000" placeholder="Bạn đang nghĩ gì thế?"></textarea>
                    <label class="hb-upload-zone" for="select_post_img">
                        <img src="" style="display:none" id="post_img" alt="Xem trước ảnh bài đăng">
                        <span class="hb-upload-placeholder"><span class="hb-upload-icon"><i class="bi bi-image"></i></span><strong>Thêm ảnh JPG hoặc PNG</strong><small>Tối đa 2 MB</small></span>
                    </label>
                    <input class="visually-hidden" name="post_img" type="file" id="select_post_img" accept="image/jpeg,image/png" required>
                </div>
                <div class="modal-footer"><button type="button" class="hb-secondary-button" data-bs-dismiss="modal">Hủy</button><button type="submit" class="hb-primary-button hb-grow-button">Đăng bài</button></div>
            </form>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end hb-side-panel" tabindex="-1" id="notification_sidebar" aria-labelledby="notificationTitle">
    <div class="offcanvas-header">
        <div><h5 class="offcanvas-title" id="notificationTitle">Thông báo</h5><small>Những cập nhật mới nhất dành cho bạn.</small></div>
        <button type="button" class="hb-icon-button" data-bs-dismiss="offcanvas" aria-label="Đóng"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="hb-panel-filter"><button class="is-active" type="button">Tất cả</button><button type="button">Chưa đọc</button></div>
    <div class="offcanvas-body">
        <?php $notifications = getNotifications(50, 0); foreach ($notifications as $not): ?>
            <a class="hb-notification-item <?= (int) $not['read_status'] === 0 ? 'is-unread' : '' ?>" href="?u=<?= rawurlencode($not['from_username']) ?>">
                <img class="hb-avatar hb-avatar-md" src="public/images/profile/<?= e($not['from_profile_pic']) ?>" alt="">
                <span class="hb-notification-copy"><strong><?= e($not['from_first_name'] . ' ' . $not['from_last_name']) ?></strong><span><?= e($not['message']) ?></span><time class="timeago" datetime="<?= e($not['created_at']) ?>"></time></span>
                <?php if ((int) $not['read_status'] === 0): ?><span class="hb-unread-dot" aria-label="Chưa đọc"></span><?php endif; ?>
            </a>
        <?php endforeach; if (empty($notifications)): ?>
            <div class="hb-panel-empty"><i class="bi bi-bell"></i><strong>Chưa có thông báo</strong><span>Các tương tác mới sẽ xuất hiện ở đây.</span></div>
        <?php endif; ?>
    </div>
</div>

<div class="offcanvas offcanvas-end hb-side-panel" tabindex="-1" id="message_sidebar" aria-labelledby="messagesTitle">
    <div class="offcanvas-header">
        <div><h5 class="offcanvas-title" id="messagesTitle">Tin nhắn</h5><small>Các cuộc trò chuyện gần đây.</small></div>
        <button type="button" class="hb-icon-button" data-bs-dismiss="offcanvas" aria-label="Đóng"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="hb-panel-search"><i class="bi bi-search"></i><span>Tìm cuộc trò chuyện</span></div>
    <div class="offcanvas-body" id="chatlist"><div class="hb-panel-empty"><span class="spinner-border spinner-border-sm" role="status"></span><strong>Đang tải tin nhắn...</strong></div></div>
</div>

<div class="modal fade hb-chat-modal" id="chatbox" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header hb-chat-header">
                <a href="#" id="cplink" class="hb-chat-person"><img src="public/images/profile/default_profile.jpg" id="chatter_pic" alt=""><span><strong id="chatter_name">Đang tải...</strong><small>@<span id="chatter_username">...</span></small></span></a>
                <div class="hb-chat-header-actions"><button type="button" class="hb-icon-button" aria-label="Tìm trong cuộc trò chuyện"><i class="bi bi-search"></i></button><button type="button" class="hb-icon-button" data-bs-dismiss="modal" aria-label="Đóng"><i class="bi bi-x-lg"></i></button></div>
            </div>
            <div class="modal-body d-flex flex-column-reverse gap-2" id="user_chat"></div>
            <div class="modal-footer hb-chat-composer-wrap">
                <div id="msgsender" class="hb-chat-composer"><button type="button" class="hb-chat-plus" aria-label="Thêm"><i class="bi bi-plus-lg"></i></button><input type="text" maxlength="2000" id="msginput" placeholder="Aa" autocomplete="off"><button id="sendmsg" type="button" aria-label="Gửi tin nhắn"><i class="bi bi-send-fill"></i></button></div>
                <div id="blerror" class="hb-alert hb-alert-danger" style="display:none"><i class="bi bi-slash-circle"></i><span>Không thể nhắn tin do trạng thái chặn.</span></div>
            </div>
        </div>
    </div>
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
