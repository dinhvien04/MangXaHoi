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

## Tài khoản và phân quyền

Handbook dùng **một bảng `users`, một form đăng nhập và một session xác thực** cho cả User lẫn Admin.

- `role = 'User'`: đăng nhập xong vào mạng xã hội.
- `role = 'Admin'`: đăng nhập bằng cùng form, hệ thống tự chuyển tới `/admin/`.
- Admin vẫn là một tài khoản Handbook bình thường và có thể quay lại giao diện mạng xã hội bằng chính session đó.
- Quyền Admin được kiểm tra lại từ database trên mỗi action/API quản trị. Nếu bị hạ role, quyền Control Center mất ngay nhưng tài khoản vẫn có thể tiếp tục dùng phần User.
- Không có form đăng nhập Admin riêng và không có chức năng Admin giả mạo/đăng nhập thành người dùng khác.

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
5. Mở `http://localhost/MangXaHoi/` và đăng nhập tại form chung.
6. Tài khoản có role Admin sẽ được chuyển tới `http://localhost/MangXaHoi/admin/`; User thường không được vào Control Center.

Database có thể cấu hình bằng `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`.

## Nâng cấp database cũ

**Backup database trước**. Với database từ phiên bản cũ, chạy migration hardening nếu chưa chạy:

```text
database/migrations/20260909_backend_hardening.sql
```

Sau đó chạy migration cleanup cho mô hình đăng nhập chung:

```text
database/migrations/20260912_unified_auth.sql
```

Migration thứ hai chỉ loại bỏ cột `password_text` cũ; có thể chạy lại an toàn nếu cột này đã được xóa trước đó.

## Backend hardening

Backend hiện áp dụng:

- session cookie `HttpOnly`, `SameSite=Lax`, strict-mode, idle/absolute timeout;
- một session role-aware cho User/Admin và revalidate quyền từ database ở action/API nhạy cảm;
- tài khoản bị block, bị xóa hoặc admin bị hạ quyền không thể tiếp tục dùng quyền cũ;
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
- `tests/backend/integration.php` có hơn 60 assertion cho auth/session, CSRF/OTP, duplicate constraints, follow/block, message, search, like/comment/post ownership, moderation, role-based Admin access, privilege revocation và cascade delete.

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
