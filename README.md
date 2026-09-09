# Handbook - Ứng dụng mạng xã hội

Handbook là ứng dụng mạng xã hội viết bằng PHP/MySQL. Hệ thống hỗ trợ đăng ký, đăng nhập, xác minh email bằng OTP, hồ sơ cá nhân, bài đăng, like, bình luận, follow, block, tìm kiếm, thông báo, nhắn tin và trang quản trị.

## Kiến trúc

Dự án dùng kiến trúc **feature-based** đơn giản, tách rõ 3 phần:

```text
MangXaHoi/
├── backend/       # PHP nghiệp vụ, database, auth, API/action handlers
├── frontend/      # Toàn bộ giao diện user/admin
├── public/        # CSS, JavaScript, hình ảnh và AdminLTE
├── admin/         # entry point /admin, không chứa giao diện/nghiệp vụ
├── config/        # cấu hình database/SMTP
├── database/      # schema/dữ liệu SQL
└── index.php      # entry point cho user
```

Backend được chia theo chức năng (`auth`, `users`, `posts`, `interactions`, `messages`, `notifications`, `search`, `admin`) thay vì MVC nhiều tầng. Frontend không nằm trong backend.

Xem chi tiết tại [ARCHITECTURE.md](ARCHITECTURE.md).

## Yêu cầu

- PHP 7.4+ (khuyến nghị PHP 8.x)
- MySQL/MariaDB
- PHP extensions: `mysqli`, `fileinfo`
- Apache/Nginx hoặc XAMPP/WAMP tương đương

## Cài đặt

1. Clone repository vào web root, ví dụ `htdocs/MangXaHoi`.
2. Tạo database `handbook` và import `database/handbook.sql`.
3. Database mặc định dùng `localhost`, user `root`, password rỗng. Có thể cấu hình bằng biến môi trường:
   - `DB_HOST`
   - `DB_NAME`
   - `DB_USER`
   - `DB_PASS`
4. Để dùng OTP/email, copy `config/smtp.example.php` thành `config/smtp.php` và điền tài khoản SMTP. `config/smtp.php` đã được gitignore và không được commit credential thật.
5. Mở `http://localhost/MangXaHoi/`.
6. Trang quản trị: `http://localhost/MangXaHoi/admin/`.

## Chức năng chính

### Người dùng

- Đăng ký / đăng nhập / đăng xuất.
- Xác minh email OTP, quên và đổi mật khẩu.
- Xem/chỉnh sửa hồ sơ và ảnh đại diện.
- Tạo, chỉnh sửa, xóa, báo cáo bài viết.
- Like/unlike, bình luận.
- Follow/unfollow, block/unblock.
- Tìm kiếm người dùng.
- Thông báo và nhắn tin.

### Quản trị viên

- Đăng nhập admin.
- Dashboard thống kê.
- Tìm kiếm, xác minh, block/unblock, xóa người dùng.
- Thay đổi vai trò User/Admin.
- Cập nhật hồ sơ admin.
- Tìm kiếm, duyệt và xóa bài đăng/bình luận.

## Phát triển

- Nghiệp vụ PHP mới đặt trong `backend/<feature>/`.
- UI mới đặt trong `frontend/user/` hoặc `frontend/admin/`.
- CSS/JS/image phía trình duyệt đặt trong `public/`.
- Không viết SQL trực tiếp trong template mới.
- Không đưa mật khẩu database/SMTP thật lên GitHub.
