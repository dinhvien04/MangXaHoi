# Handbook - Ứng dụng mạng xã hội

Handbook là ứng dụng mạng xã hội PHP/MySQL hỗ trợ đăng ký, đăng nhập, OTP email, hồ sơ, bài đăng, like, bình luận, follow, block, tìm kiếm, thông báo, nhắn tin và quản trị.

## Kiến trúc

```text
MangXaHoi/
├── backend/       # business logic, auth, database, JSON API/action handlers
├── frontend/      # giao diện user/admin
├── public/        # CSS, JavaScript, hình ảnh, ảnh upload, AdminLTE
├── config/        # database/SMTP
├── database/      # schema + migrations
├── tests/         # backend integration tests
├── .htaccess      # rewrite /admin + chặn truy cập implementation files
└── index.php      # front controller chung
```

Backend được chia theo feature (`auth`, `users`, `posts`, `interactions`, `messages`, `notifications`, `search`, `admin`) thay vì classic MVC nhiều tầng. Frontend không nằm trong backend.

## Yêu cầu

- PHP 8.1+ (khuyến nghị PHP 8.2+)
- MySQL 8 / MariaDB tương thích
- PHP extensions: `mysqli`, `fileinfo`, `mbstring`
- Apache/XAMPP có `mod_rewrite` và `AllowOverride` cho `.htaccess`
- Composer (khuyến nghị để dùng PHPMailer 7.1.1; repo vẫn giữ fallback mailer cũ để clone XAMPP hiện tại không bị gãy ngay)

## Cài mới

1. Clone repository vào web root, ví dụ `htdocs/MangXaHoi`.
2. Khuyến nghị chạy `composer install` để dùng PHPMailer 7.1.1. Nếu chưa có Composer, mailer tương thích cũ vẫn được dùng làm fallback tạm thời.
3. Tạo database `handbook` và import `database/handbook.sql`.
4. Copy `config/smtp.example.php` thành `config/smtp.php` và điền SMTP nếu cần email/OTP.
5. Mở `http://localhost/MangXaHoi/`.
6. Admin: `http://localhost/MangXaHoi/admin/`.

Database có thể cấu hình bằng `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`.

## Nâng cấp database cũ

**Backup database trước**, sau đó chạy một lần:

```text
database/migrations/20260909_backend_hardening.sql
```

Migration thêm foreign key/cascade, unique constraint cho email/username/like/follow/block, index hiệu năng, bảng rate-limit và chuẩn hóa cột moderation/notification. Nếu database cũ đã có email hoặc username trùng nhau, xử lý dữ liệu trùng trước khi chạy migration.

## Backend hardening

Backend hiện áp dụng:

- session cookie `HttpOnly`, `SameSite=Lax`, strict-mode, idle/absolute timeout;
- revalidate user/admin từ database ở mỗi action/API nhạy cảm;
- tài khoản bị block, bị xóa hoặc admin bị hạ quyền không thể tiếp tục dùng session cũ;
- CSRF synchronizer token cho state-changing request;
- action/API thay đổi trạng thái chỉ nhận `POST`;
- prepared statements + database FK/UNIQUE để chống orphan/duplicate data;
- OTP 5 phút, tối đa 5 lần thử, resend cooldown và rate limiting;
- upload chỉ nhận ảnh JPEG/PNG hợp lệ, giới hạn kích thước, tên file ngẫu nhiên;
- API trả JSON dữ liệu, không render HTML trong backend;
- pagination/limit cho feed, chat, notification, search;
- moderation và block được enforce ở query backend;
- xóa user/post dọn dữ liệu liên quan bằng cascade và dọn file upload;
- `backend/`, `frontend/`, `config/`, `database/`, `tests/`, `vendor/` không được truy cập trực tiếp từ web.

## Test

GitHub Actions workflow `Backend Tests` chạy:

- `php -l` cho PHP backend/frontend/tests;
- import schema sạch vào MySQL 8;
- `tests/backend/integration.php` hiện có hơn 60 assertion cho auth/session, CSRF/OTP, duplicate constraints, follow/block, message, search, like/comment/post ownership, moderation, admin privilege revocation và cascade delete.

Chạy local sau khi có database test đã import:

```text
DB_NAME=handbook_test DB_USER=root DB_PASS=... php tests/backend/integration.php
```

Trên Windows PowerShell hãy đặt các biến môi trường tương ứng trước khi chạy PHP.

## Quy ước phát triển

- Nghiệp vụ PHP: `backend/<feature>/`.
- UI: `frontend/user/` hoặc `frontend/admin/`.
- Browser assets: `public/`.
- API mới trả JSON thuần; HTML được render ở frontend.
- Không đưa credential thật hoặc `config/smtp.php` lên GitHub.
