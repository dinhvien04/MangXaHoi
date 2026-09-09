# Handbook - Ứng dụng mạng xã hội

Handbook là ứng dụng mạng xã hội viết bằng PHP/MySQL, hỗ trợ đăng ký, đăng nhập, xác minh email, bài đăng, like, bình luận, follow, block, tìm kiếm, thông báo, nhắn tin và trang quản trị.

## Chức năng

- Đăng ký, đăng nhập, đăng xuất.
- Xác minh email bằng OTP và quên mật khẩu.
- Xem và chỉnh sửa hồ sơ.
- Tạo, chỉnh sửa, xóa và báo cáo bài viết.
- Like / unlike và bình luận.
- Follow / unfollow, block / unblock.
- Tìm kiếm người dùng.
- Thông báo tương tác.
- Nhắn tin giữa người dùng.
- Admin quản lý người dùng, bài viết và báo cáo.

## Kiến trúc

Project đã được refactor sang **feature-based architecture**. Mỗi chức năng có thư mục riêng:

```text
app/
├── Core/
├── Auth/
├── Users/
├── Posts/
├── Interactions/
│   ├── Likes/
│   ├── Comments/
│   ├── Follow/
│   └── Block/
├── Messages/
├── Notifications/
├── Search/
├── Admin/
├── Shared/
└── Support/

routes/
├── web.php
├── user-actions.php
├── api.php
├── admin-actions.php
└── admin-api.php

config/
└── database.php

database/
└── handbook.sql

assets/js/
├── app.js
└── features/
    ├── posts.js
    ├── follow.js
    ├── likes.js
    ├── comments.js
    ├── search.js
    ├── notifications.js
    └── messages.js
```

`index.php` chỉ còn là front controller. Các file cũ như `assets/php/functions.php`, `assets/php/actions.php` và `assets/php/ajax.php` được giữ làm compatibility wrapper để giao diện hiện tại chưa bị phá.

Xem giải thích đầy đủ trong **[ARCHITECTURE.md](ARCHITECTURE.md)**.

## Cài đặt

1. Clone repository:

```bash
git clone https://github.com/dinhvien04/MangXaHoi.git
```

2. Tạo database `handbook` và import `database/handbook.sql`.

3. Database mặc định:

```text
host: localhost
name: handbook
user: root
password: (trống)
```

Có thể thay bằng các biến môi trường `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.

4. Tạo file `assets/php/smtp_config.php`:

```php
<?php
return [
    'username' => 'your-email@gmail.com',
    'password' => 'your-app-password',
];
```

File này đã được `.gitignore` và không được commit credential thật.

5. Đặt project trong thư mục web server, ví dụ `htdocs/MangXaHoi`, sau đó truy cập:

```text
http://localhost/MangXaHoi
```

Trang Admin:

```text
http://localhost/MangXaHoi/admin
```

## Công nghệ

- PHP
- MySQL / MariaDB
- HTML / CSS / JavaScript
- jQuery
- Bootstrap
- PHPMailer

## Quy tắc code mới

- Không viết nghiệp vụ mới trong `assets/php/functions.php`.
- Không viết SQL trong template giao diện.
- Chức năng nào thì code trong `app/<Feature>/` tương ứng.
- Request/redirect/AJAX xử lý trong `routes/`.
- JavaScript theo chức năng nằm trong `assets/js/features/`.
- Thành phần dùng chung nằm trong `app/Core/`, `app/Shared/` hoặc `app/Support/`.
