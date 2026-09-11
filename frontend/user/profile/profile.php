<?php
$isOwnProfile = (int) $user['id'] === (int) $profile['id'];
$isBlockedRelation = checkBS($profile['id']);
?>
<div class="hb-profile-page">
    <section class="hb-profile-hero">
        <div class="hb-profile-cover">
            <div class="hb-cover-brand"><span>HANDBOOK</span><small>Connect. Share. Belong.</small></div>
        </div>
        <div class="hb-profile-info-card">
            <img class="hb-profile-avatar" src="public/images/profile/<?= e($profile['profile_pic']) ?>" alt="Ảnh đại diện của <?= e($profile['first_name']) ?>">
            <div class="hb-profile-copy">
                <h1><?= e($profile['first_name'] . ' ' . $profile['last_name']) ?></h1>
                <p>@<?= e($profile['username']) ?></p>
                <?php if (!$isBlockedRelation): ?>
                    <div class="hb-profile-stats">
                        <span><strong><?= count($profilePosts) ?></strong> bài đăng</span>
                        <span><strong><?= count($profile['followers']) ?></strong> người theo dõi</span>
                        <span><strong><?= count($profile['following']) ?></strong> đang theo dõi</span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="hb-profile-actions">
                <?php if ($isOwnProfile): ?>
                    <button class="hb-primary-button" type="button" data-bs-toggle="modal" data-bs-target="#addpost"><i class="bi bi-plus-lg"></i> Thêm bài viết</button>
                    <a class="hb-secondary-button" href="?editprofile"><i class="bi bi-pencil"></i> Chỉnh sửa hồ sơ</a>
                <?php elseif (!$isBlockedRelation): ?>
                    <?php if (checkBlockStatus($user['id'], $profile['id'])): ?>
                        <button class="hb-danger-button unblockbtn" type="button" data-user-id="<?= (int) $profile['id'] ?>"><i class="bi bi-unlock"></i> Mở chặn</button>
                    <?php elseif (checkBlockStatus($profile['id'], $user['id'])): ?>
                        <span class="hb-status-pill hb-status-danger"><i class="bi bi-slash-circle"></i> Người dùng đã chặn bạn</span>
                    <?php elseif (checkFollowStatus($profile['id'])): ?>
                        <button class="hb-secondary-button unfollowbtn" type="button" data-user-id="<?= (int) $profile['id'] ?>"><i class="bi bi-person-check"></i> Đang theo dõi</button>
                    <?php else: ?>
                        <button class="hb-primary-button followbtn" type="button" data-user-id="<?= (int) $profile['id'] ?>"><i class="bi bi-person-plus"></i> Theo dõi</button>
                    <?php endif; ?>
                    <?php if (!checkBlockStatus($profile['id'], $user['id']) && !checkBlockStatus($user['id'], $profile['id'])): ?>
                        <button class="hb-secondary-button" type="button" data-bs-toggle="modal" data-bs-target="#chatbox" onclick="popchat(<?= (int) $profile['id'] ?>)"><i class="bi bi-chat-dots"></i> Nhắn tin</button>
                        <div class="dropdown">
                            <button class="hb-icon-button hb-profile-more" type="button" data-bs-toggle="dropdown" aria-label="Tùy chọn"><i class="bi bi-three-dots"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end hb-dropdown-menu">
                                <li><form method="post" action="?action=block_user" onsubmit="return confirm('Chặn người dùng này?')"><?= csrfField() ?><input type="hidden" name="user_id" value="<?= (int) $profile['id'] ?>"><button class="dropdown-item text-danger" type="submit"><i class="bi bi-slash-circle"></i> Chặn người dùng</button></form></li>
                            </ul>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <nav class="hb-profile-tabs" aria-label="Điều hướng hồ sơ">
                <a class="is-active" href="#profile-posts">Bài viết</a>
                <a href="#profile-about">Giới thiệu</a>
                <a href="#profile-about">Người theo dõi</a>
                <a href="#profile-posts">Ảnh</a>
            </nav>
        </div>
    </section>

    <div class="hb-profile-content">
        <aside class="hb-profile-about hb-card" id="profile-about">
            <h2>Giới thiệu</h2>
            <div class="hb-about-row"><span class="hb-about-icon"><i class="bi bi-person-badge"></i></span><span><strong><?= e($profile['first_name'] . ' ' . $profile['last_name']) ?></strong><small>Thành viên Handbook Social</small></span></div>
            <div class="hb-about-row"><span class="hb-about-icon"><i class="bi bi-at"></i></span><span><strong>@<?= e($profile['username']) ?></strong><small>Tên người dùng</small></span></div>
            <?php if (!$isBlockedRelation): ?>
                <div class="hb-about-row"><span class="hb-about-icon"><i class="bi bi-people"></i></span><span><strong><?= count($profile['followers']) ?> người theo dõi</strong><small><?= count($profile['following']) ?> đang theo dõi</small></span></div>
            <?php endif; ?>
            <?php if ($isOwnProfile): ?><a class="hb-secondary-button hb-about-edit" href="?editprofile">Chỉnh sửa chi tiết</a><?php endif; ?>
        </aside>

        <main class="hb-profile-feed" id="profile-posts">
            <?php if ($isOwnProfile): ?>
                <section class="hb-card hb-composer-card">
                    <div class="hb-composer-row"><img class="hb-avatar hb-avatar-md" src="public/images/profile/<?= e($user['profile_pic']) ?>" alt=""><button class="hb-composer-input" type="button" data-bs-toggle="modal" data-bs-target="#addpost"><?= e($user['first_name']) ?> ơi, bạn đang nghĩ gì thế?</button></div>
                    <div class="hb-card-divider"></div>
                    <div class="hb-composer-actions"><button type="button" data-bs-toggle="modal" data-bs-target="#addpost"><i class="bi bi-image text-success"></i><span>Ảnh / Video</span></button><button type="button" data-bs-toggle="modal" data-bs-target="#addpost"><i class="bi bi-emoji-smile text-warning"></i><span>Cảm xúc</span></button><button type="button" data-bs-toggle="modal" data-bs-target="#addpost"><i class="bi bi-calendar-event text-danger"></i><span>Sự kiện</span></button></div>
                </section>
            <?php endif; ?>

            <?php if ($isBlockedRelation): ?>
                <section class="hb-card hb-empty-state"><span class="hb-empty-icon"><i class="bi bi-eye-slash"></i></span><h2>Không thể xem bài viết</h2><p>Quan hệ chặn giữa hai tài khoản đang được áp dụng.</p></section>
            <?php elseif (!$profilePosts): ?>
                <section class="hb-card hb-empty-state"><span class="hb-empty-icon"><i class="bi bi-images"></i></span><h2>Chưa có bài viết</h2><p><?= $isOwnProfile ? 'Hãy chia sẻ bài viết đầu tiên của bạn.' : 'Người dùng này chưa đăng bài nào.' ?></p></section>
            <?php else: ?>
                <?php foreach ($profilePosts as $post): $likes = getLikes($post['id']); $comments = getComments($post['id'], 50, 0); ?>
                    <article class="hb-card hb-post-card" id="post-<?= (int) $post['id'] ?>">
                        <header class="hb-post-header">
                            <div class="hb-post-author"><img class="hb-avatar hb-avatar-md" src="public/images/profile/<?= e($profile['profile_pic']) ?>" alt=""><span><strong><?= e($profile['first_name'] . ' ' . $profile['last_name']) ?></strong><small><?= e(show_time($post['created_at'])) ?> · <i class="bi bi-globe2"></i></small></span></div>
                            <div class="dropdown">
                                <button class="hb-icon-button" type="button" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end hb-dropdown-menu">
                                    <?php if ($isOwnProfile): ?>
                                        <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#profilePost<?= (int) $post['id'] ?>"><i class="bi bi-arrows-fullscreen"></i> Xem chi tiết</button></li>
                                        <li><form method="post" action="?action=delete_post" onsubmit="return confirm('Xóa bài viết này?')"><?= csrfField() ?><input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>"><button class="dropdown-item text-danger" type="submit"><i class="bi bi-trash3"></i> Xóa bài viết</button></form></li>
                                    <?php else: ?>
                                        <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#profilePost<?= (int) $post['id'] ?>"><i class="bi bi-arrows-fullscreen"></i> Xem chi tiết</button></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </header>
                        <?php if ($post['post_text'] !== ''): ?><div class="hb-post-copy"><?= nl2br(e($post['post_text'])) ?></div><?php endif; ?>
                        <button class="hb-post-media-button" type="button" data-bs-toggle="modal" data-bs-target="#profilePost<?= (int) $post['id'] ?>" aria-label="Xem chi tiết bài viết"><img src="public/images/posts/<?= e($post['post_img']) ?>" class="hb-post-media" alt="Bài đăng"></button>
                        <div class="hb-post-stats"><span><span class="hb-like-dot"><i class="bi bi-hand-thumbs-up-fill"></i></span> <span id="likecount<?= (int) $post['id'] ?>"><?= count($likes) ?></span></span><span><?= count($comments) ?> bình luận</span></div>
                        <div class="hb-card-divider"></div>
                        <div class="hb-post-actions">
                            <?php $liked = checkLikeStatus($post['id']); ?>
                            <button class="hb-post-action like_btn" type="button" style="display:<?= $liked ? 'none' : '' ?>" data-post-id="<?= (int) $post['id'] ?>"><i class="bi bi-hand-thumbs-up"></i><span>Thích</span></button>
                            <button class="hb-post-action unlike_btn is-liked" type="button" style="display:<?= $liked ? '' : 'none' ?>" data-post-id="<?= (int) $post['id'] ?>"><i class="bi bi-hand-thumbs-up-fill"></i><span>Đã thích</span></button>
                            <button class="hb-post-action" type="button" data-bs-toggle="modal" data-bs-target="#profilePost<?= (int) $post['id'] ?>"><i class="bi bi-chat"></i><span>Bình luận</span></button>
                            <button class="hb-post-action" type="button" onclick="shareProfilePost(<?= (int) $post['id'] ?>)"><i class="bi bi-share"></i><span>Chia sẻ</span></button>
                        </div>
                    </article>

                    <div class="modal fade hb-post-detail-modal" id="profilePost<?= (int) $post['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered">
                            <div class="modal-content">
                                <button type="button" class="hb-modal-close" data-bs-dismiss="modal" aria-label="Đóng"><i class="bi bi-x-lg"></i></button>
                                <div class="hb-post-detail-grid">
                                    <div class="hb-post-detail-media"><img src="public/images/posts/<?= e($post['post_img']) ?>" alt="Bài đăng"></div>
                                    <div class="hb-post-detail-side">
                                        <div class="hb-post-header"><div class="hb-post-author"><img class="hb-avatar hb-avatar-md" src="public/images/profile/<?= e($profile['profile_pic']) ?>" alt=""><span><strong><?= e($profile['first_name'].' '.$profile['last_name']) ?></strong><small><?= e(show_time($post['created_at'])) ?> · Công khai</small></span></div></div>
                                        <?php if ($post['post_text'] !== ''): ?><div class="hb-post-copy hb-post-detail-copy"><?= nl2br(e($post['post_text'])) ?></div><?php endif; ?>
                                        <div class="hb-post-stats"><span><?= count($likes) ?> lượt thích</span><span><?= count($comments) ?> bình luận</span></div>
                                        <div class="hb-card-divider"></div>
                                        <div class="hb-detail-comments" id="profile-comments-<?= (int) $post['id'] ?>">
                                            <?php foreach ($comments as $comment): ?><div class="hb-comment-item"><img class="hb-avatar hb-avatar-sm" src="public/images/profile/<?= e($comment['profile_pic']) ?>" alt=""><div class="hb-comment-main"><div class="hb-comment-bubble"><a href="?u=<?= rawurlencode($comment['username']) ?>"><?= e($comment['first_name'].' '.$comment['last_name']) ?></a><p><?= e($comment['comment']) ?></p></div><small><?= e(show_time($comment['created_at'])) ?></small></div></div><?php endforeach; ?>
                                        </div>
                                        <?php if (checkFollowStatus($profile['id']) || $isOwnProfile): ?>
                                            <div class="hb-comment-composer hb-detail-comment-composer"><img class="hb-avatar hb-avatar-sm" src="public/images/profile/<?= e($user['profile_pic']) ?>" alt=""><div class="hb-comment-input-wrap"><input class="comment-input" maxlength="2000" placeholder="Viết bình luận..."><button class="add-comment" type="button" data-cs="profile-comments-<?= (int) $post['id'] ?>" data-post-id="<?= (int) $post['id'] ?>"><i class="bi bi-send-fill"></i></button></div></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>
</div>
<script>
function shareProfilePost(postId) {
    const url = new URL(window.location.href); url.hash = 'post-' + postId;
    const data = {title:'Handbook Social', text:'Xem bài viết này trên Handbook', url:url.toString()};
    if (navigator.share) { navigator.share(data).catch(() => {}); return; }
    navigator.clipboard?.writeText(url.toString()).then(() => alert('Đã sao chép liên kết bài viết.')).catch(() => prompt('Sao chép liên kết:', url.toString()));
}
</script>
