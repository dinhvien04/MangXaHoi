<div class="hb-app-shell">
    <aside class="hb-left-rail" aria-label="Điều hướng nhanh">
        <div class="hb-rail-sticky">
            <a class="hb-user-summary" href="?u=<?= rawurlencode($user['username']) ?>">
                <img src="public/images/profile/<?= e($user['profile_pic']) ?>" alt="Ảnh đại diện">
                <span><strong><?= e($user['first_name'] . ' ' . $user['last_name']) ?></strong><small>@<?= e($user['username']) ?></small></span>
            </a>
            <div class="hb-rail-nav">
                <a class="hb-rail-link is-active" href="./"><span class="hb-rail-icon"><i class="bi bi-house-door-fill"></i></span><span>Trang chủ</span></a>
                <button class="hb-rail-link" type="button" onclick="document.getElementById('search')?.focus()"><span class="hb-rail-icon"><i class="bi bi-compass"></i></span><span>Khám phá</span></button>
                <button class="hb-rail-link" type="button" data-bs-toggle="offcanvas" data-bs-target="#notification_sidebar"><span class="hb-rail-icon"><i class="bi bi-bell"></i></span><span>Thông báo</span></button>
                <button class="hb-rail-link" type="button" data-bs-toggle="offcanvas" data-bs-target="#message_sidebar"><span class="hb-rail-icon"><i class="bi bi-chat-dots"></i></span><span>Tin nhắn</span></button>
                <a class="hb-rail-link" href="?u=<?= rawurlencode($user['username']) ?>"><span class="hb-rail-icon"><i class="bi bi-person"></i></span><span>Trang cá nhân</span></a>
                <button class="hb-rail-link" type="button" data-bs-toggle="modal" data-bs-target="#addpost"><span class="hb-rail-icon"><i class="bi bi-plus-square"></i></span><span>Tạo bài viết</span></button>
            </div>
            <div class="hb-rail-divider"></div>
            <p class="hb-rail-heading">Handbook Social</p>
            <div class="hb-rail-note">
                <span class="hb-rail-note-icon"><i class="bi bi-people-fill"></i></span>
                <div><strong>Kết nối cộng đồng</strong><small>Theo dõi, chia sẻ và trò chuyện với mọi người.</small></div>
            </div>
        </div>
    </aside>

    <main class="hb-feed-column">
        <?= showError('post_img') ?>
        <?= showError('post_text') ?>

        <section class="hb-card hb-composer-card" aria-label="Tạo bài viết">
            <div class="hb-composer-row">
                <img class="hb-avatar hb-avatar-md" src="public/images/profile/<?= e($user['profile_pic']) ?>" alt="">
                <button class="hb-composer-input" type="button" data-bs-toggle="modal" data-bs-target="#addpost"><?= e($user['first_name']) ?> ơi, bạn đang nghĩ gì thế?</button>
            </div>
            <div class="hb-card-divider"></div>
            <div class="hb-composer-actions">
                <button type="button" data-bs-toggle="modal" data-bs-target="#addpost" data-action="open-post-photo"><i class="bi bi-image text-success"></i><span>Ảnh / Video</span></button>
                <button type="button" data-bs-toggle="modal" data-bs-target="#addpost" onclick="setTimeout(()=>document.querySelector('#post_text_input')?.focus(),300)"><i class="bi bi-emoji-smile text-warning"></i><span>Cảm xúc</span></button>
                <button type="button" data-bs-toggle="modal" data-bs-target="#addpost" onclick="setTimeout(()=>document.querySelector('#post_text_input')?.focus(),300)"><i class="bi bi-calendar-event text-danger"></i><span>Sự kiện</span></button>
            </div>
        </section>

        <?php if (!$posts): ?>
            <section class="hb-card hb-empty-state">
                <span class="hb-empty-icon"><i class="bi bi-newspaper"></i></span>
                <h2>Feed của bạn đang trống</h2>
                <p>Theo dõi một vài người hoặc đăng bài đầu tiên để bắt đầu.</p>
                <button class="hb-primary-button" type="button" data-bs-toggle="modal" data-bs-target="#addpost"><i class="bi bi-plus-lg"></i> Tạo bài viết</button>
            </section>
        <?php endif; ?>

        <?php foreach ($posts as $post): $comments = getComments($post['id'], 3, 0); ?>
            <article class="hb-card hb-post-card" id="post-<?= (int) $post['id'] ?>">
                <header class="hb-post-header">
                    <a class="hb-post-author" href="?u=<?= rawurlencode($post['username']) ?>">
                        <img class="hb-avatar hb-avatar-md" src="public/images/profile/<?= e($post['profile_pic']) ?>" alt="">
                        <span><strong><?= e($post['first_name'] . ' ' . $post['last_name']) ?></strong><small><?= show_time($post['created_at']) ?> · <i class="bi bi-globe2"></i></small></span>
                    </a>
                    <div class="dropdown">
                        <button class="hb-icon-button" type="button" data-bs-toggle="dropdown" aria-label="Tùy chọn bài viết"><i class="bi bi-three-dots"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end hb-dropdown-menu">
                            <?php if ((int) $post['uid'] === (int) $user['id']): ?>
                                <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#editPost<?= (int) $post['id'] ?>"><i class="bi bi-pencil"></i> Chỉnh sửa bài viết</button></li>
                                <li><form method="post" action="?action=delete_post" onsubmit="return confirm('Xóa bài viết này?')"><?= csrfField() ?><input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>"><button class="dropdown-item text-danger" type="submit"><i class="bi bi-trash3"></i> Xóa bài viết</button></form></li>
                            <?php else: ?>
                                <li><button class="dropdown-item" type="button" onclick="reportPost(<?= (int) $post['id'] ?>)"><i class="bi bi-flag"></i> Báo cáo vi phạm</button></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </header>

                <?php if ($post['post_text'] !== ''): ?>
                    <div class="hb-post-copy"><?= nl2br(e($post['post_text'])) ?></div>
                <?php endif; ?>

                <?php if (!empty($post['post_img']) && is_file(dirname(__DIR__, 3) . '/public/images/posts/' . basename((string) $post['post_img']))): ?>
                    <div class="hb-post-media-wrap">
                        <img src="public/images/posts/<?= e($post['post_img']) ?>" loading="lazy" class="hb-post-media post-image" alt="Ảnh bài đăng của <?= e($post['first_name']) ?>">
                    </div>
                <?php endif; ?>

                <div class="hb-post-stats">
                    <span><span class="hb-like-dot"><i class="bi bi-hand-thumbs-up-fill"></i></span> <span id="likecount<?= (int) $post['id'] ?>"><?= (int) $post['like_count'] ?></span></span>
                    <span><?= (int) $post['comment_count'] ?> bình luận</span>
                </div>

                <div class="hb-card-divider"></div>
                <div class="hb-post-actions">
                    <button class="hb-post-action like_btn" type="button" style="display:<?= !empty($post['liked_by_me']) ? 'none' : '' ?>" data-post-id="<?= (int) $post['id'] ?>"><i class="bi bi-hand-thumbs-up"></i><span>Thích</span></button>
                    <button class="hb-post-action unlike_btn is-liked" type="button" style="display:<?= !empty($post['liked_by_me']) ? '' : 'none' ?>" data-post-id="<?= (int) $post['id'] ?>"><i class="bi bi-hand-thumbs-up-fill"></i><span>Đã thích</span></button>
                    <button class="hb-post-action" type="button" onclick="document.querySelector('#comment-input-<?= (int) $post['id'] ?>')?.focus()"><i class="bi bi-chat"></i><span>Bình luận</span></button>
                    <button class="hb-post-action" type="button" onclick="sharePost(<?= (int) $post['id'] ?>, '<?= e($post['username']) ?>')"><i class="bi bi-share"></i><span>Chia sẻ</span></button>
                </div>
                <div class="hb-card-divider"></div>

                <div class="hb-comment-list" id="comment-section<?= (int) $post['id'] ?>">
                    <?php if (!$comments): ?><p class="hb-empty-copy nce">Chưa có bình luận nào.</p><?php endif; ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="hb-comment-item">
                            <img class="hb-avatar hb-avatar-sm" src="public/images/profile/<?= e($comment['profile_pic']) ?>" alt="">
                            <div class="hb-comment-main">
                                <div class="hb-comment-bubble"><a href="?u=<?= rawurlencode($comment['username']) ?>"><?= e($comment['first_name'] . ' ' . $comment['last_name']) ?></a><p><?= e($comment['comment']) ?></p></div>
                                <small><?= show_time($comment['created_at']) ?></small>
                            </div>
                            <?php if ((int) $comment['user_id'] === (int) $user['id']): ?>
                                <form method="post" action="?action=delete_comment" class="hb-comment-delete"><?= csrfField() ?><input type="hidden" name="comment_id" value="<?= (int) $comment['id'] ?>"><button type="submit" aria-label="Xóa bình luận"><i class="bi bi-trash3"></i></button></form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="hb-comment-composer">
                    <img class="hb-avatar hb-avatar-sm" src="public/images/profile/<?= e($user['profile_pic']) ?>" alt="">
                    <div class="hb-comment-input-wrap">
                        <input id="comment-input-<?= (int) $post['id'] ?>" type="text" maxlength="2000" class="comment-input" placeholder="Viết bình luận..." aria-label="Viết bình luận">
                        <button class="add-comment" type="button" data-page="wall" data-cs="comment-section<?= (int) $post['id'] ?>" data-post-id="<?= (int) $post['id'] ?>" aria-label="Gửi bình luận"><i class="bi bi-send-fill"></i></button>
                    </div>
                </div>
            </article>

            <?php if ((int) $post['uid'] === (int) $user['id']): ?>
                <div class="modal fade hb-modal" id="editPost<?= (int) $post['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <form class="modal-content" action="?action=update_post" method="post">
                            <?= csrfField() ?>
                            <div class="modal-header"><div><h5 class="modal-title">Chỉnh sửa bài viết</h5><small>Cập nhật nội dung bài viết của bạn.</small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body">
                                <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
                                <div class="hb-modal-user"><img class="hb-avatar hb-avatar-md" src="public/images/profile/<?= e($user['profile_pic']) ?>" alt=""><span><strong><?= e($user['first_name'] . ' ' . $user['last_name']) ?></strong><small>Công khai</small></span></div>
                                <textarea name="post_content" maxlength="10000" class="hb-textarea" rows="6"><?= e($post['post_text']) ?></textarea>
                                <div class="hb-current-image"><img src="public/images/posts/<?= e($post['post_img']) ?>" alt="Ảnh hiện tại"><span>Ảnh hiện tại</span></div>
                            </div>
                            <div class="modal-footer"><button type="button" class="hb-secondary-button" data-bs-dismiss="modal">Hủy</button><button class="hb-primary-button" type="submit">Cập nhật</button></div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </main>

    <aside class="hb-right-rail" aria-label="Gợi ý kết nối">
        <div class="hb-rail-sticky hb-right-stack">
            <section class="hb-right-section">
                <div class="hb-section-heading"><h2>Gợi ý cho bạn</h2></div>
                <?php foreach ($followSuggestions as $suggestion): ?>
                    <div class="hb-suggestion-row">
                        <a class="hb-suggestion-user" href="?u=<?= rawurlencode($suggestion['username']) ?>">
                            <img class="hb-avatar hb-avatar-md" src="public/images/profile/<?= e($suggestion['profile_pic']) ?>" alt="">
                            <span><strong><?= e($suggestion['first_name'] . ' ' . $suggestion['last_name']) ?></strong><small>@<?= e($suggestion['username']) ?></small></span>
                        </a>
                        <button class="hb-follow-button followbtn" type="button" data-user-id="<?= (int) $suggestion['id'] ?>">Theo dõi</button>
                    </div>
                <?php endforeach; ?>
                <?php if (!$followSuggestions): ?><p class="hb-empty-copy text-start">Chưa có gợi ý mới.</p><?php endif; ?>
            </section>
            <div class="hb-rail-divider"></div>
            <section class="hb-right-section">
                <div class="hb-section-heading"><h2>Truy cập nhanh</h2></div>
                <button class="hb-contact-row" type="button" data-bs-toggle="offcanvas" data-bs-target="#message_sidebar"><span class="hb-contact-icon"><i class="bi bi-chat-square-dots-fill"></i></span><span><strong>Tin nhắn</strong><small>Xem các cuộc trò chuyện gần đây</small></span></button>
                <button class="hb-contact-row" type="button" data-bs-toggle="offcanvas" data-bs-target="#notification_sidebar"><span class="hb-contact-icon"><i class="bi bi-bell-fill"></i></span><span><strong>Thông báo</strong><small>Cập nhật tương tác mới nhất</small></span></button>
            </section>
        </div>
    </aside>
</div>

<script>
function reportPost(postId) {
    if (!confirm('Bạn có chắc muốn báo cáo bài viết này không?')) return;
    const body = new URLSearchParams({post_id:String(postId)});
    window.handbookFetch('?action=report_post', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:body.toString()})
        .then(async response => { const data = await response.json(); alert(data.message || 'Đã xử lý yêu cầu.'); })
        .catch(() => alert('Không thể báo cáo bài viết.'));
}
function sharePost(postId, username) {
    const url = new URL(window.location.href);
    url.search = '?u=' + encodeURIComponent(username);
    url.hash = 'post-' + postId;
    const shareData = {title:'Handbook Social', text:'Xem bài viết này trên Handbook', url:url.toString()};
    if (navigator.share) { navigator.share(shareData).catch(() => {}); return; }
    navigator.clipboard?.writeText(url.toString()).then(() => alert('Đã sao chép liên kết bài viết.')).catch(() => prompt('Sao chép liên kết bài viết:', url.toString()));
}
</script>
