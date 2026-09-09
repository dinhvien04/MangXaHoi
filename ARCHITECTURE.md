# Kiến trúc dự án MangXaHoi

Dự án được tổ chức theo **feature-based architecture**: code của từng chức năng nằm trong thư mục riêng thay vì dồn toàn bộ nghiệp vụ vào `functions.php`, `actions.php`, `ajax.php` và một file JavaScript lớn.

## Cây thư mục chính

```text
MangXaHoi/
├── app/
│   ├── bootstrap.php
│   ├── Core/
│   │   ├── database.php
│   │   ├── forms.php
│   │   ├── http.php
│   │   ├── password.php
│   │   └── view.php
│   ├── Auth/
│   │   ├── functions.php
│   │   └── views/
│   ├── Users/
│   │   ├── functions.php
│   │   ├── presenters.php
│   │   └── views/
│   ├── Posts/
│   │   ├── functions.php
│   │   └── views/
│   ├── Interactions/
│   │   ├── Block/functions.php
│   │   ├── Comments/functions.php
│   │   ├── Follow/functions.php
│   │   └── Likes/functions.php
│   ├── Messages/functions.php
│   ├── Notifications/functions.php
│   ├── Search/functions.php
│   ├── Admin/functions.php
│   ├── Shared/views/
│   └── Support/time.php
├── routes/
│   ├── web.php
│   ├── user-actions.php
│   ├── api.php
│   ├── admin-actions.php
│   └── admin-api.php
├── config/
│   └── database.php
├── database/
│   └── handbook.sql
├── assets/
│   ├── css/
│   ├── images/
│   ├── js/
│   │   ├── app.js
│   │   └── features/
│   │       ├── posts.js
│   │       ├── follow.js
│   │       ├── likes.js
│   │       ├── comments.js
│   │       ├── search.js
│   │       ├── notifications.js
│   │       └── messages.js
│   ├── pages/        # template cũ, chỉ còn được gọi qua view wrapper
│   └── php/          # compatibility entry points + PHPMailer
├── admin/            # giao diện Admin hiện tại và static AdminLTE
└── index.php          # front controller rất mỏng
```

## Chức năng nằm ở đâu?

| Muốn sửa | Thư mục |
|---|---|
| Đăng ký, đăng nhập, OTP, quên mật khẩu | `app/Auth/` |
| Hồ sơ người dùng | `app/Users/` |
| Bài đăng | `app/Posts/` |
| Like | `app/Interactions/Likes/` |
| Bình luận | `app/Interactions/Comments/` |
| Follow | `app/Interactions/Follow/` |
| Block | `app/Interactions/Block/` |
| Nhắn tin | `app/Messages/` |
| Thông báo | `app/Notifications/` |
| Tìm kiếm | `app/Search/` |
| Quản trị | `app/Admin/` |
| Routing trang | `routes/web.php` |
| Form actions User | `routes/user-actions.php` |
| AJAX User | `routes/api.php` |
| Actions Admin | `routes/admin-actions.php` |
| AJAX Admin | `routes/admin-api.php` |
| Database config | `config/database.php` + `app/Core/database.php` |
| Database schema | `database/handbook.sql` |
| JavaScript theo chức năng | `assets/js/features/` |

## Luồng request

### Trang web

```text
index.php
  -> app/bootstrap.php
  -> routes/web.php
  -> feature functions
  -> feature view
```

### Form User

Các form cũ vẫn gửi đến `assets/php/actions.php` để không phá giao diện hiện tại. File này bây giờ chỉ là compatibility wrapper:

```text
assets/php/actions.php
  -> routes/user-actions.php
  -> app/<Feature>/functions.php
  -> database
```

### AJAX User

```text
assets/php/ajax.php
  -> routes/api.php
  -> app/<Feature>/functions.php
  -> database
```

Admin hoạt động tương tự với `admin/php/*` và `routes/admin-*`.

## Vì sao vẫn còn `assets/php/functions.php`?

Các template giao diện cũ đang gọi trực tiếp các hàm như `getUser()`, `getLikes()`, `getComments()`... Vì vậy file `assets/php/functions.php` được giữ lại như một **wrapper tương thích**, nhưng không còn chứa nghiệp vụ. Nó chỉ load `app/bootstrap.php`.

Tương tự, `assets/php/actions.php`, `assets/php/ajax.php` và các file `admin/php/*` cũ chỉ còn nhiệm vụ chuyển request sang `routes/`.

Điều này cho phép refactor kiến trúc mà không phá giao diện cũ. Khi giao diện mới được viết lại, các template trong `assets/pages/` có thể được xóa hoàn toàn.

## Frontend JavaScript

File `assets/js/custom.js` cũ đã được bỏ. Logic JavaScript được chia theo feature:

```text
posts.js          -> preview ảnh bài đăng
follow.js         -> follow / unfollow / unblock
likes.js          -> like / unlike
comments.js       -> thêm bình luận
search.js         -> tìm kiếm người dùng
notifications.js  -> trạng thái thông báo
messages.js       -> chat và polling tin nhắn
app.js            -> bootstrap dùng chung, timeago
```

## Nguyên tắc phát triển từ bây giờ

1. Không thêm nghiệp vụ mới vào `assets/php/functions.php`.
2. Không thêm logic mới vào `assets/php/actions.php` hoặc `assets/php/ajax.php`.
3. Mọi nghiệp vụ mới phải nằm trong thư mục feature tương ứng.
4. Route chỉ nhận request, kiểm tra quyền, gọi feature và trả response/redirect.
5. SQL chỉ nằm trong feature/core, không viết SQL trực tiếp trong template mới.
6. UI mới đặt trong `app/<Feature>/views/` hoặc `app/Shared/views/`.
7. JavaScript mới phải đặt đúng feature trong `assets/js/features/` nếu không phải code dùng chung.
8. Static image/CSS vẫn nằm trong `assets/`.
9. Không đưa credential SMTP hoặc mật khẩu database thật lên GitHub.

## Giai đoạn tiếp theo

Các file trong `assets/pages/` hiện là UI cũ và các view mới trong `app/*/views/` đang đóng vai trò adapter. Đây là chủ ý: backend và JavaScript đã được chia theo feature trước, sau đó có thể thay toàn bộ UI từng feature mà không phải sửa lại nghiệp vụ hoặc database.
