<header class="hb-topbar" role="banner">
    <div class="hb-topbar-left">
        <a class="hb-brand" href="./" aria-label="Handbook Social - Trang chủ">
            <span class="hb-brand-mark">H</span>
            <span class="hb-brand-name">Handbook</span>
        </a>
        <form class="hb-global-search" id="searchform" onsubmit="return false;" role="search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" id="search" placeholder="Tìm kiếm trên Handbook" autocomplete="off" aria-label="Tìm kiếm trên Handbook">
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
        <a class="hb-topnav-item is-active" href="./" aria-current="page"><i class="bi bi-house-door-fill"></i><span>Trang chủ</span></a>
        <button class="hb-topnav-item" type="button" data-bs-toggle="modal" data-bs-target="#addpost"><i class="bi bi-plus-square"></i><span>Tạo bài</span></button>
        <button class="hb-topnav-item" type="button" data-bs-toggle="offcanvas" data-bs-target="#message_sidebar" aria-controls="message_sidebar"><i class="bi bi-chat-dots-fill"></i><span>Tin nhắn</span></button>
        <button class="hb-topnav-item" type="button" id="show_not" data-bs-toggle="offcanvas" data-bs-target="#notification_sidebar" aria-controls="notification_sidebar">
            <span class="hb-nav-icon-wrap"><i class="bi bi-bell-fill"></i><?php $unread = getUnreadNotificationsCount(); if ($unread > 0): ?><span class="hb-nav-badge un-count"><?= (int) $unread ?></span><?php endif; ?></span>
            <span>Thông báo</span>
        </button>
    </nav>

    <div class="hb-topbar-actions">
        <button class="hb-circle-action" type="button" data-bs-toggle="modal" data-bs-target="#addpost" aria-label="Tạo bài viết"><i class="bi bi-plus-lg"></i></button>
        <button class="hb-circle-action position-relative" type="button" data-bs-toggle="offcanvas" data-bs-target="#message_sidebar" aria-label="Tin nhắn">
            <i class="bi bi-chat-dots-fill"></i><span class="hb-nav-badge hb-message-badge" id="msgcounter"></span>
        </button>
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
                        <span><strong><?= e($user['first_name'] . ' ' . $user['last_name']) ?></strong><small>@<?= e($user['username']) ?></small></span>
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="?u=<?= rawurlencode($user['username']) ?>"><i class="bi bi-person-circle"></i> Hồ sơ của tôi</a></li>
                <li><a class="dropdown-item" href="?editprofile"><i class="bi bi-pencil-square"></i> Chỉnh sửa hồ sơ</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><form method="post" action="?action=logout" class="m-0"><?= csrfField() ?><button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right"></i> Đăng xuất</button></form></li>
            </ul>
        </div>
    </div>
</header>
