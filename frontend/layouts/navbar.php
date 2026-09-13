<header class="hb-topbar" role="banner">
    <div class="hb-topbar-left">
        <a class="hb-brand" href="./" aria-label="Novera Social - Trang chủ">
            <span class="hb-brand-mark">N</span>
            <span class="hb-brand-name">Novera</span>
        </a>
        <form class="hb-global-search" id="searchform" onsubmit="return false;" role="search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" id="search" placeholder="Tìm kiếm trên Novera" autocomplete="off" aria-label="Tìm kiếm trên Novera">
            <div class="hb-search-results" style="display:none" id="search_result">
                <div class="hb-panel-title-row">
                    <strong>Tìm kiếm</strong>
                    <button type="button" class="hb-icon-button hb-icon-button-sm" id="close_search" aria-label="Đóng tìm kiếm"><i class="bi bi-x-lg"></i></button>
                </div>
                <div id="sra"><p class="hb-empty-copy">Nhập tên hoặc tên người dùng</p></div>
            </div>
        </form>
    </div>

    <nav class="hb-topbar-nav" aria-label="Điều hướng chính">
        <a class="hb-topnav-item is-active" href="./" aria-current="page"><span class="hb-nav-glyph">N</span><span>Trang chủ</span></a>
        <button class="hb-topnav-item" type="button" onclick="document.getElementById('search')?.focus()"><span class="hb-nav-glyph">K</span><span>Khám phá</span></button>
        <button class="hb-topnav-item" type="button" data-bs-toggle="offcanvas" data-bs-target="#message_sidebar" aria-controls="message_sidebar"><span class="hb-nav-glyph">M</span><span>Tin nhắn</span></button>
        <button class="hb-topnav-item" type="button" id="show_not" data-bs-toggle="offcanvas" data-bs-target="#notification_sidebar" aria-controls="notification_sidebar">
            <span class="hb-nav-icon-wrap"><span class="hb-nav-glyph">N</span><?php $unread = getUnreadNotificationsCount(); if ($unread > 0): ?><span class="hb-nav-badge un-count"><?= (int) $unread ?></span><?php endif; ?></span>
            <span>Thông báo</span>
        </button>
    </nav>

    <div class="hb-topbar-actions">
        <?php if ((string)($user['role'] ?? 'User') === 'Admin'): ?>
            <a class="hb-circle-action" href="admin/" aria-label="Mở Novera Control Center" title="Novera Control Center"><i class="bi bi-shield-lock-fill"></i></a>
        <?php endif; ?>
        <button class="hb-circle-action" type="button" data-bs-toggle="modal" data-bs-target="#addpost" aria-label="Tạo bài viết"><i class="bi bi-plus-lg"></i></button>
        <button class="hb-circle-action position-relative" type="button" data-bs-toggle="offcanvas" data-bs-target="#message_sidebar" aria-label="Tin nhắn">
            <i class="bi bi-chat-dots-fill"></i><span class="hb-nav-badge hb-message-badge" id="msgcounter"></span>
        </button>
        <button class="hb-circle-action" type="button" aria-label="Tùy chọn"><i class="bi bi-three-dots"></i></button>
        <div class="dropdown">
            <button class="hb-profile-trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="public/images/profile/<?= e($user['profile_pic']) ?>" alt="Ảnh đại diện của <?= e($user['first_name']) ?>">
                <span><?= e($user['first_name'] . ' ' . $user['last_name']) ?></span>
                <i class="bi bi-chevron-down"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end hb-dropdown-menu">
                <li class="px-2 pt-2 pb-1">
                    <a class="hb-dropdown-profile" href="?u=<?= rawurlencode($user['username']) ?>">
                        <img src="public/images/profile/<?= e($user['profile_pic']) ?>" alt="">
                        <span><strong><?= e($user['first_name'] . ' ' . $user['last_name']) ?></strong><small>@<?= e($user['username']) ?> · <?= e($user['role']) ?></small></span>
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="?u=<?= rawurlencode($user['username']) ?>"><i class="bi bi-person-circle"></i> Hồ sơ của tôi</a></li>
                <li><a class="dropdown-item" href="?editprofile"><i class="bi bi-pencil-square"></i> Chỉnh sửa hồ sơ</a></li>
                <?php if ((string)($user['role'] ?? 'User') === 'Admin'): ?>
                    <li><a class="dropdown-item" href="admin/"><i class="bi bi-shield-lock-fill"></i> Novera Control Center</a></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li><form method="post" action="?action=logout" class="m-0"><?= csrfField() ?><button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right"></i> Đăng xuất</button></form></li>
            </ul>
        </div>
    </div>

    <div class="hb-mobile-top-actions" aria-label="Tác vụ nhanh">
        <button type="button" aria-label="Tìm kiếm" onclick="const form=document.querySelector('.hb-global-search'); form?.classList.toggle('is-mobile-open'); setTimeout(()=>document.getElementById('search')?.focus(),0)"><i class="bi bi-search"></i></button>
        <button type="button" data-bs-toggle="offcanvas" data-bs-target="#message_sidebar" aria-label="Tin nhắn">M</button>
    </div>
</header>

<nav class="hb-mobile-bottom-nav" aria-label="Điều hướng di động">
    <a class="is-active" href="./" aria-current="page"><span class="hb-mobile-nav-glyph">N</span><span>Home</span></a>
    <button type="button" onclick="const form=document.querySelector('.hb-global-search'); form?.classList.add('is-mobile-open'); setTimeout(()=>document.getElementById('search')?.focus(),0)"><span class="hb-mobile-nav-glyph">F</span><span>Theo dõi</span></button>
    <button type="button" data-bs-toggle="modal" data-bs-target="#addpost" aria-label="Tạo bài viết"><span class="hb-mobile-nav-glyph">+</span><span>Tạo</span></button>
    <button type="button" data-bs-toggle="offcanvas" data-bs-target="#notification_sidebar"><span class="hb-mobile-nav-glyph">N</span><span>Thông báo</span></button>
    <a href="?u=<?= rawurlencode($user['username']) ?>"><span class="hb-mobile-nav-glyph">P</span><span>Hồ sơ</span></a>
</nav>
