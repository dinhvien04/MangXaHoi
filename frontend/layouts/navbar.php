<nav class="navbar navbar-expand-lg navbar-light bg-white border">
    <div class="container col-lg-9 col-sm-12 col-md-10 d-flex justify-content-between">
        <div class="d-flex align-items-center gap-3 col-lg-8 col-sm-12">
            <a class="navbar-brand" href="./"><img src="public/images/handbook.png" alt="Handbook" height="28"></a>
            <form class="d-flex position-relative flex-grow-1" id="searchform" onsubmit="return false;">
                <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left:14px"></i>
                <input class="form-control ps-5" type="search" id="search" placeholder="Tìm kiếm" autocomplete="off" style="border-radius:50px">
                <div class="bg-white rounded border shadow py-3 px-3 mt-5" style="display:none;position:absolute;z-index:99;top:10px;left:0;width:100%;max-height:300px;overflow-y:auto" id="search_result">
                    <button type="button" class="btn-close float-end" id="close_search"></button>
                    <div id="sra" class="text-start"><p class="text-center text-muted">Nhập tên hoặc tên người dùng</p></div>
                </div>
            </form>
        </div>
        <ul class="navbar-nav flex-row justify-content-evenly gap-2">
            <li class="nav-item"><a class="nav-link text-dark" href="./"><i class="bi bi-house-door-fill"></i></a></li>
            <li class="nav-item"><a class="nav-link text-dark" data-bs-toggle="modal" data-bs-target="#addpost" href="#"><i class="bi bi-plus-square-fill"></i></a></li>
            <li class="nav-item">
                <a class="nav-link text-dark position-relative" id="show_not" data-bs-toggle="offcanvas" href="#notification_sidebar">
                    <i class="bi bi-bell-fill"></i>
                    <?php $unread = getUnreadNotificationsCount(); if ($unread > 0): ?>
                        <span class="un-count position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><small><?= (int) $unread ?></small></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item"><a class="nav-link text-dark position-relative" data-bs-toggle="offcanvas" href="#message_sidebar"><i class="bi bi-chat-right-dots-fill"></i><span class="un-count position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="msgcounter"></span></a></li>
            <li class="nav-item dropdown">
                <a class="nav-link" href="#" data-bs-toggle="dropdown"><img src="public/images/profile/<?= e($user['profile_pic']) ?>" alt="" height="30" width="30" class="rounded-circle border"></a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="?u=<?= rawurlencode($user['username']) ?>"><i class="bi bi-person"></i> Hồ sơ của tôi</a></li>
                    <li><a class="dropdown-item" href="?editprofile"><i class="bi bi-pencil-square"></i> Chỉnh sửa hồ sơ</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><form method="post" action="?action=logout" class="m-0"><?= csrfField() ?><button class="dropdown-item" type="submit"><i class="bi bi-box-arrow-in-left"></i> Đăng xuất</button></form></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
