<link href="assets/css/custom.css" rel="stylesheet">
<?php global $user; ?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border">
    <div
        class="container col-lg-9 col-sm-12 col-md-10 d-flex flex-lg-row flex-md-row flex-sm-column justify-content-between">
        <div class="d-flex justify-content-between col-lg-8 col-sm-12">
            <a class="navbar-brand" href="?">
                <img src="assets/images/handbook.png" alt="" height="28">

            </a>
            <form class="d-flex position-relative" id="searchform" style="width: 100%; max-width: 400px; margin: auto;">
                <!-- Biểu tượng -->
                <i class="bi bi-search position-absolute top-50 start-3 translate-middle text-muted"
                    style="font-size: 1.2rem; left: 10px;"></i>

                <!-- Input -->
                <input class="form-control ps-5" type="search" id="search" placeholder="Tìm kiếm" aria-label="Search"
                    autocomplete="off"
                    style="padding-left: 2.5rem; border-radius: 50px; border: 1px solid #ddd; transition: all 0.3s ease;">

                <!-- Kết quả tìm kiếm -->
                <div class="bg-white text-end rounded border shadow py-3 px-4 mt-5"
                    style="display:none; position:absolute; z-index:99; top: calc(100% + 5px); left: 0; width: 100%; max-height: 300px; overflow-y: auto; transition: opacity 0.3s ease, transform 0.3s ease;"
                    id="search_result">
                    <button type="button" class="btn-close" aria-label="Close" id="close_search"
                        style="position: absolute; top: 10px; right: 10px; font-size: 0.9rem; color: #6c757d;"></button>
                    <div id="sra" class="text-start">
                        <p class="text-center text-muted">Nhập tên hoặc tên người dùng</p>
                    </div>
                </div>
            </form>

            <script>
            document.addEventListener("DOMContentLoaded", function() {
                const searchInput = document.getElementById("search");
                const searchResult = document.getElementById("search_result");
                const closeSearch = document.getElementById("close_search");

                // Hiển thị kết quả khi nhập
                searchInput.addEventListener("input", function() {
                    if (this.value.trim() !== "") {
                        searchResult.style.display = "block";
                        searchResult.style.opacity = "1";
                        searchResult.style.transform = "translateY(0)";
                    } else {
                        searchResult.style.display = "none";
                    }
                });

                // Đóng kết quả tìm kiếm khi bấm nút đóng
                closeSearch.addEventListener("click", function() {
                    searchResult.style.display = "none";
                });

                // Ẩn kết quả khi click ngoài
                document.addEventListener("click", function(event) {
                    if (!searchResult.contains(event.target) && !searchInput.contains(event.target)) {
                        searchResult.style.display = "none";
                    }
                });
            });
            </script>


        </div>


        <ul class="navbar-nav flex-fill flex-row justify-content-evenly mb-lg-1 mb-sm-0">
                <!-- HOME -->
            <li class="nav-item">
                <a class="nav-link text-dark" href="?"><i class="bi bi-house-door-fill"></i></a>
            </li>
            <!-- THÊM POST -->
            <li class="nav-item">

                <a class="nav-link text-dark" data-bs-toggle="modal" data-bs-target="#addpost" href="#"><i
                        class="bi bi-plus-square-fill"></i></a>
            </li>
            <!-- THông báo -->
            <li class="nav-item">


                <?php
                if (getUnreadNotificationsCount() > 0) {
                ?>
                <a class="nav-link text-dark position-relative" id="show_not" data-bs-toggle="offcanvas"
                    href="#notification_sidebar" role="button" aria-controls="offcanvasExample">
                    <i class="bi bi-bell-fill"></i>
                    <span class="un-count position-absolute start-10 translate-middle badge p-1 rounded-pill bg-danger">
                        <small><?= getUnreadNotificationsCount() ?></small>
                    </span>
                </a>

                <?php
                } else {
                ?>
                <a class="nav-link text-dark" data-bs-toggle="offcanvas" href="#notification_sidebar" role="button"
                    aria-controls="offcanvasExample"><i class="bi bi-bell-fill"></i></a>
                <?php
                }
                ?>


            </li>
            <!-- TIN NHẮN -->
            <li class="nav-item">
                <a class="nav-link text-dark" data-bs-toggle="offcanvas" href="#message_sidebar" href="#"><i
                        class="bi bi-chat-right-dots-fill"></i> <span
                        class="un-count position-absolute start-10 translate-middle badge p-1 rounded-pill bg-danger"
                        id="msgcounter">

                    </span></a>
            </li>
            <!-- ACCOUNT -->
            <li class="nav-item dropdown dropstart">
                <a class="nav-link" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <img src="assets/images/profile/<?= $user['profile_pic'] ?>" alt="" height="30" width="30"
                        class="rounded-circle border">
                </a>
                <ul class="dropdown-menu position-absolute top-100 end-50" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="?u=<?= $user['username'] ?>"><i class="bi bi-person"></i> Hồ sơ
                            của tôi</a></li>

                    <li><a class="dropdown-item" href="?editprofile"><i class="bi bi-pencil-square"></i>Chỉnh sửa hồ
                            sơ</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="assets/php/actions.php?logout"><i
                                class="bi bi-box-arrow-in-left"></i> Đăng xuất</a></li>
                </ul>
            </li>

        </ul>


    </div>
</nav>