# Kiến trúc dự án MangXaHoi

## Mục tiêu

Repository được tổ chức theo **feature-based architecture** với nguyên tắc quan trọng nhất: **frontend và backend tách riêng**. Dự án không dùng classic MVC nhiều tầng; một chức năng có thể được tìm thấy trực tiếp theo tên thư mục.

## Cây thư mục

```text
MangXaHoi/
├── backend/
│   ├── bootstrap.php
│   ├── core/
│   │   ├── database.php
│   │   ├── forms.php
│   │   ├── http.php
│   │   └── password.php
│   ├── auth/
│   │   ├── functions.php
│   │   └── mail/
│   ├── users/
│   ├── posts/
│   ├── interactions/
│   │   ├── block/
│   │   ├── comments/
│   │   ├── follow/
│   │   └── likes/
│   ├── messages/
│   ├── notifications/
│   ├── search/
│   ├── admin/
│   ├── support/
│   └── http/
│       ├── user-actions.php
│       ├── api.php
│       ├── admin-actions.php
│       └── admin-api.php
├── frontend/
│   ├── render.php
│   ├── router.php
│   ├── layouts/
│   ├── user/
│   │   ├── auth/
│   │   ├── profile/
│   │   └── posts/
│   └── admin/
├── public/
│   ├── css/
│   ├── images/
│   ├── js/
│   │   ├── features/
│   │   └── admin/
│   └── admin/          # static AdminLTE assets
├── admin/
│   └── index.php       # public entry point cho /admin/
├── config/
│   ├── database.php
│   └── smtp.example.php
├── database/
│   └── handbook.sql
└── index.php
```

## Phân chia trách nhiệm

### `backend/`

Chỉ chứa PHP xử lý ứng dụng: database, session/auth, validation, nghiệp vụ và request handlers. Không chứa giao diện HTML của user/admin.

| Chức năng | Vị trí |
|---|---|
| Đăng ký/đăng nhập/OTP/quên mật khẩu | `backend/auth/` |
| Hồ sơ người dùng | `backend/users/` |
| Bài đăng | `backend/posts/` |
| Like/bình luận/follow/block | `backend/interactions/` |
| Tin nhắn | `backend/messages/` |
| Thông báo | `backend/notifications/` |
| Tìm kiếm | `backend/search/` |
| Nghiệp vụ admin | `backend/admin/` |
| Form/API entry handlers | `backend/http/` |

`backend/bootstrap.php` khởi tạo session, database và load các feature dùng chung.

### `frontend/`

Chỉ chứa phần hiển thị. User UI nằm trong `frontend/user/`, admin UI nằm trong `frontend/admin/`, layout dùng chung nằm trong `frontend/layouts/`.

Template có thể render dữ liệu và điều kiện hiển thị nhưng nghiệp vụ/database mới không được đặt tại đây.

### `public/`

Chứa tài nguyên được trình duyệt truy cập trực tiếp: CSS, JS, hình ảnh, ảnh upload và static AdminLTE. Không chứa business logic PHP.

### `admin/index.php`

Thư mục `admin/` được giữ lại duy nhất để URL `/admin/` tiếp tục hoạt động. File này chỉ bootstrap, dispatch action/API và chọn frontend admin; toàn bộ nghiệp vụ nằm trong `backend/`, toàn bộ UI nằm trong `frontend/admin/`.

## Luồng request

### User page

```text
index.php
  -> backend/bootstrap.php
  -> frontend/router.php
  -> frontend/layouts + frontend/user/*
```

### User form action

```text
index.php?action=...
  -> backend/bootstrap.php
  -> backend/http/user-actions.php
  -> backend/<feature>/functions.php
  -> redirect/response
```

### User AJAX

```text
index.php?api=...
  -> backend/bootstrap.php
  -> backend/http/api.php
  -> backend/<feature>/functions.php
  -> JSON
```

### Admin

```text
admin/index.php
  -> backend/bootstrap.php
  -> frontend/admin/*
```

Admin action/API được dispatch tương tự qua `backend/http/admin-actions.php` và `backend/http/admin-api.php`.

## Authentication và session

Session được khởi tạo một lần trong `backend/bootstrap.php`. User sử dụng `$_SESSION['Auth']` và `$_SESSION['userdata']`; admin sử dụng `$_SESSION['admin_auth']`. Các handler bắt buộc đăng nhập gọi `requireUserAuth()` hoặc `requireAdminAuth()`.

Mật khẩu mới được hash bằng `password_hash()`. Code cũ vẫn hỗ trợ nâng cấp hash khi đăng nhập để giữ tương thích dữ liệu hiện có.

## Upload

Ảnh bài đăng nằm trong `public/images/posts/`; ảnh hồ sơ nằm trong `public/images/profile/`. Backend kiểm tra kích thước và MIME (`image/jpeg`, `image/png`) trước khi lưu, sau đó sinh tên file ngẫu nhiên.

## Thêm feature mới

Ví dụ thêm chức năng bookmark:

1. Tạo `backend/bookmarks/functions.php` cho nghiệp vụ/SQL.
2. Load file đó từ `backend/bootstrap.php`.
3. Thêm action/API cần thiết trong `backend/http/`.
4. Tạo UI trong `frontend/user/bookmarks/`.
5. Tạo JS trong `public/js/features/bookmarks.js` nếu cần.

Không tạo `Controllers/Models/Views/Services/Repositories` chỉ để bọc một feature đơn giản.

## Migration từ kiến trúc cũ

Kiến trúc trước sử dụng `app/`, `routes/`, `assets/pages/`, `assets/php/` và `admin/php/` compatibility wrappers. Sau migration:

- `app/` không còn là implementation root.
- `routes/` đã được thay bằng `backend/http/`.
- `assets/pages/` được thay bằng `frontend/`.
- `assets/css`, `assets/js`, `assets/images` được chuyển sang `public/`.
- `assets/php/` và `admin/php/` wrappers bị loại bỏ.
- AdminLTE được chuyển thành static asset trong `public/admin/`.

Do đó cây thư mục hiện tại chính là implementation thực tế, không phải một lớp wrapper đặt phía trên code cũ.
