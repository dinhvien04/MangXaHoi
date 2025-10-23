
<?php if(isset($_SESSION['Auth'])){ ?>
<div class="modal fade" id="addpost" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
  <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm bài đăng mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img src="" style="display:none" id="post_img" class="w-100 rounded border">
                <form method="post" action="assets/php/actions.php?addpost" enctype="multipart/form-data">
                    <div class="my-3">
                        <input class="form-control" name="post_img" type="file" id="select_post_img">
                    </div>
                    <div class="mb-3">
                        <label for="exampleFormControlTextarea1" class="form-label">Nói gì đó đi</label>
                        <textarea name="post_text" class="form-control" id="exampleFormControlTextarea1" rows="1"></textarea>
                    </div>
    
                <button type="submit" class="btn btn-primary">Đăng</button>
          
                </form>
            </div>
   
        </div>
  </div>
</div>

<!-- Notification Sidebar -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="notification_sidebar" aria-labelledby="offcanvasExampleLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasExampleLabel">Thông báo</h5>
  </div>
  <div class="offcanvas-body">
    <?php
    $notifications = getNotifications();
    foreach ($notifications as $not) {
        $time = $not['created_at'];
        $fuser = getUser($not['from_user_id']);
        $post = '';
        if ($not['post_id']) {
            $post = 'data-bs-toggle="modal" data-bs-target="#postview' . $not['post_id'] . '"';
        }
    ?>
    <div class="d-flex justify-content-between align-items-center border-bottom p-3">
      <div class="d-flex align-items-center">
        <img src="assets/images/profile/<?= $fuser['profile_pic'] ?>" alt="" height="40" width="40" class="rounded-circle border">
        <div class="ms-2">
          <a href="?u=<?= $fuser['username'] ?>" class="text-decoration-none text-dark fw-bold">
            <?= $fuser['first_name'] ?> <?= $fuser['last_name'] ?>
          </a>
          <p class="mb-0 text-muted small <?= $not['read_status'] ? 'text-secondary' : '' ?>">
            <?= $not['message'] ?>
          </p>
          <time class="text-muted small timeago <?= $not['read_status'] ? 'text-secondary' : '' ?>" datetime="<?= $time ?>"></time>
        </div>
      </div>
      <div>
        <?php if ($not['read_status'] == 0) { ?>
          <span class="badge bg-primary">New</span>
        <?php } else if ($not['read_status'] == 2) { ?>
          <span class="badge bg-danger">Xoá</span>
        <?php } ?>
      </div>
    </div>
    <?php } ?>
    <?php if (count($notifications) == 0) { ?>
      <p class="text-center text-muted">Không có thông báo nào</p>
    <?php } ?>
  </div>
</div>
<!-- Message Sidebar -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="message_sidebar" aria-labelledby="offcanvasExampleLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasExampleLabel">Tin nhắn</h5>
  </div>
  <div class="offcanvas-body" id="chatlist">
    <!-- Messages will be dynamically loaded here -->
    <p class="text-center text-muted">Đang tải tin nhắn...</p>
  </div>
</div>

<!-- Chat Modal -->
<div class="modal fade" id="chatbox" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <!-- Thêm màu nền blue cho phần header -->
      <div class="modal-header bg-primary text-white">
        <a href="" id="cplink" class="text-decoration-none text-white">
          <h5 class="modal-title" id="exampleModalLabel">
            <img src="assets/images/profile/default_profile.jpg" id="chatter_pic" height="40" width="40" class="m-1 rounded-circle border">
            <span id="chatter_name"></span> (@<span id="chatter_username">Đang tải...</span>)
          </h5>
        </a>
      </div>
      <div class="modal-body d-flex flex-column-reverse gap-2" id="user_chat">
        <!-- Chat content dynamically loaded -->
        <p class="text-center text-muted">Đang tải trò chuyện...</p>
      </div>
      <div class="modal-footer">
        <div id="msgsender" class="input-group">
          <input type="text" class="form-control" id="msginput" placeholder="Nói điều gì đó..." aria-label="Message input">
          <!-- Thay nút Send bằng biểu tượng máy bay -->
          <button class="btn btn-primary" id="sendmsg" data-user-id="0" type="button">
            <i class="bi bi-send"></i> <!-- Biểu tượng máy bay -->
          </button>
        </div>
      </div>
    </div>
  </div>
</div>





<?php } ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/jquery.timeago.js"></script>

    <script src="assets/js/custom.js?v=<?=time()?>"></script>
    

</body>

</html>
