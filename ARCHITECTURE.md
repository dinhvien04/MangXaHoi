# Kiến trúc dự án MangXaHoi

## Mục tiêu

Dự án dùng **feature-based architecture** và tách rõ frontend/backend. Backend là nguồn nghiệp vụ và dữ liệu; frontend có thể được thay hoàn toàn mà không phải nhúng HTML vào API.

## Cây chính

```text
MangXaHoi/
├── backend/
│   ├── bootstrap.php
│   ├── core/
│   │   ├── database.php
│   │   ├── http.php
│   │   ├── security.php
│   │   ├── password.php
│   │   └── forms.php
│   ├── auth/
│   ├── users/
│   ├── posts/
│   ├── interactions/{block,comments,follow,likes}/
│   ├── messages/
│   ├── notifications/
│   ├── search/
│   ├── admin/
│   └── http/{user-actions.php,api.php,admin-actions.php,admin-api.php}
├── frontend/
│   ├── layouts/
│   ├── user/
│   └── admin/
├── public/
├── config/
├── database/
│   └── migrations/
├── tests/backend/
├── .htaccess
└── index.php
```

## Request flow

```text
User page:       index.php -> backend/bootstrap.php -> frontend/router.php
User action:     index.php?action=... -> backend/http/user-actions.php
User JSON API:   index.php?api=... -> backend/http/api.php
Admin page:      /admin/ -> .htaccess -> index.php?admin=1 -> frontend/admin/router.php
Admin action:    /admin/?action=... -> backend/http/admin-actions.php
Admin JSON API:  /admin/?api=... -> backend/http/admin-api.php
```

Mọi state-changing action/API dùng `POST` + CSRF. API trả JSON thuần; frontend tự render giao diện.

## Authentication/session

`backend/bootstrap.php` cấu hình cookie session `HttpOnly`, `SameSite=Lax`, secure khi HTTPS, strict mode, idle timeout 30 phút và absolute timeout 12 giờ.

`requireUserAuth()` và `requireAdminAuth()` không chỉ tin session: chúng đọc lại database để kiểm tra account/role/status. Vì vậy block/xóa user hoặc hạ quyền admin có hiệu lực ở request kế tiếp.

User chưa verify chỉ được dùng luồng verify/resend/logout. User bị block không được gọi feature API/action.

## CSRF

`backend/core/security.php` tạo synchronizer token theo session. Form POST gửi `csrf_token`; AJAX gửi `X-CSRF-Token`. `public/js/security.js` tự gắn token cho form/AJAX/fetch.

Không dùng GET cho thao tác xóa, block, logout, approve, role change hoặc impersonation.

## OTP/rate limiting

OTP email và reset password:

- code 6 chữ số sinh bằng `random_int`;
- chỉ lưu SHA-256 của code trong session;
- hết hạn sau 5 phút;
- tối đa 5 lần thử;
- resend cooldown 60 giây;
- bảng `rate_limits` giới hạn login, OTP, forgot-password và message spam;
- reset token/code là single-use.

## Data integrity

Schema/migration thêm FK `ON DELETE CASCADE` cho quan hệ user/post/comment/like/follow/block/message/notification. Database cũng enforce:

```text
UNIQUE users.email
UNIQUE users.username
UNIQUE (likes.post_id, likes.user_id)
UNIQUE (follow_list.follower_id, follow_list.user_id)
UNIQUE (block_list.user_id, block_list.blocked_user_id)
```

Backend vẫn kiểm entity tồn tại trước khi follow/block/message/like/comment để trả lỗi đúng thay vì dựa hoàn toàn vào lỗi database.

## Feed/moderation

Feed chỉ lấy user active và post `is_approved=1`, đồng thời loại quan hệ block ở cả hai chiều. Feed query dùng JOIN/EXISTS thay cho query follow lặp theo từng bài, có limit/offset và kèm like/comment count.

`is_reported=1` nghĩa là bài bị gắn cờ để admin review; report của một user **không tự cho phép người đó kiểm duyệt/xóa bài của người khác**. Admin có thể approve/clear report hoặc xóa bài.

## Messaging/search/notification

- Message target phải tồn tại, active, không phải chính mình và không bị block.
- Chat list lấy latest-message theo conversation; message history giới hạn theo page thay vì load vô hạn.
- Search chỉ trả public fields của active users và loại blocked relationship.
- Notification chỉ tạo cho positive interaction cần thiết; unlike/unfollow/block/unblock không tạo notification gây nhiễu.

## Upload

Ảnh profile/post kiểm upload error, size, MIME `image/jpeg|image/png`, `getimagesize`, tên random bằng `random_bytes`. Khi DB insert/update fail, file mới được dọn; khi xóa post/user hoặc thay avatar, file cũ được dọn nếu phù hợp.

## Web-root protection

`.htaccess` cấm truy cập trực tiếp `backend/`, `frontend/`, `config/`, `database/`, `tests/`, `vendor/` và tắt directory listing. Chỉ front controller và browser assets trong `public/` cần được truy cập.

## Dependency

Composer là đường nạp PHPMailer ưu tiên và pin `7.1.1`. Repository vẫn giữ bản PHPMailer legacy đã có sẵn làm fallback tương thích cho clone XAMPP cũ; khi `vendor/autoload.php` tồn tại, backend luôn ưu tiên bản Composer. Nên chạy `composer install` khi triển khai.

## Test strategy

`.github/workflows/backend-tests.yml` tạo MySQL 8 test database, import schema, lint PHP và chạy `tests/backend/integration.php`. Khi thêm backend feature, ưu tiên thêm case vào integration test trước khi thay frontend.
