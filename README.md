# Handbook - Ứng dụng mạng xã hội 📖

![Handbook Logo](assets/images/handbook.png)

## 📝 Giới thiệu

Handbook là một ứng dụng mạng xã hội cho phép người dùng kết nối với nhau, chia sẻ bài viết và tương tác với nội dung. Ứng dụng có giao diện thân thiện với người dùng và bảng quản trị để quản lý ứng dụng.

## ✨ Tính năng

-   **Xác thực người dùng:** Đăng ký, đăng nhập và đăng xuất.
-   **Quản lý hồ sơ:** Chỉnh sửa hồ sơ của bạn, bao gồm tên, tên người dùng và ảnh đại diện.
-   **Tương tác xã hội:** Theo dõi và bỏ theo dõi người dùng, chặn và bỏ chặn người dùng.
-   **Chia sẻ nội dung:** Tạo bài viết với văn bản và hình ảnh.
-   **Tương tác:** Thích và bỏ thích bài viết, bình luận về bài viết.
-   **Thông báo:** Nhận thông báo về lượt thích, bình luận và lượt theo dõi.
-   **Bảng quản trị:** Quản lý người dùng và bài viết.

## 🚀 Cài đặt

1.  **Sao chép kho lưu trữ:**
    ```bash
    git clone https://github.com/dinhvien04/MangXaHoi.git
    ```
2.  **Nhập cơ sở dữ liệu:**
    -   Tạo một cơ sở dữ liệu mới có tên `handbook`.
    -   Nhập tệp `handbook.sql` vào cơ sở dữ liệu `handbook`.
3.  **Cấu hình kết nối cơ sở dữ liệu:**
    -   Mở `assets/php/config.php` và cập nhật thông tin đăng nhập cơ sở dữ liệu.
4.  **Cấu hình thông tin đăng nhập email:**
    -   Mở `assets/php/smtp_config.php` và cập nhật thông tin đăng nhập email.
5.  **Chạy ứng dụng:**
    -   Đặt dự án vào thư mục gốc của máy chủ web của bạn (ví dụ: `htdocs` cho XAMPP).
    -   Mở trình duyệt web của bạn và điều hướng đến `http://localhost/MangXaHoi`.

## 💻 Sử dụng

-   **Đăng ký:** Tạo một tài khoản mới.
-   **Đăng nhập:** Truy cập tài khoản của bạn.
-   **Tường:** Xem bài viết từ những người dùng bạn theo dõi.
-   **Hồ sơ:** Xem hồ sơ và bài viết của bạn.
-   **Chỉnh sửa hồ sơ:** Cập nhật thông tin hồ sơ của bạn.

## 🔒 Bảng quản trị

-   **Truy cập:** `http://localhost/MangXaHoi/admin`
-   **Đăng nhập:** Sử dụng thông tin đăng nhập quản trị viên của bạn để đăng nhập.
-   **Bảng điều khiển:** Xem thống kê về ứng dụng.
-   **Quản lý người dùng:** Xác minh, chặn và bỏ chặn người dùng.
-   **Quản lý bài viết:** Xóa bài viết.

## 🗃️ Lược đồ cơ sở dữ liệu

<details>
  <summary>Nhấp để xem chi tiết</summary>

-   **users:** Lưu trữ thông tin người dùng.
-   **posts:** Lưu trữ thông tin bài viết.
-   **likes:** Lưu trữ thông tin về lượt thích trên bài viết.
-   **comments:** Lưu trữ bình luận về bài viết.
-   **follow_list:** Lưu trữ thông tin về lượt theo dõi của người dùng.
-   **block_list:** Lưu trữ thông tin về người dùng bị chặn.
-   **notifications:** Lưu trữ thông báo của người dùng.

</details>

## 🛠️ Công nghệ sử dụng

![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white)
![HTML5](https://img.shields.io/badge/html5-%23E34F26.svg?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/css3-%231572B6.svg?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/javascript-%23323330.svg?style=for-the-badge&logo=javascript&logoColor=%23F7DF1E)
![jQuery](https://img.shields.io/badge/jquery-%230769AD.svg?style=for-the-badge&logo=jquery&logoColor=white)
![Bootstrap](https://img.shields.io/badge/bootstrap-%23563D7C.svg?style=for-the-badge&logo=bootstrap&logoColor=white)
![MySQL](https://img.shields.io/badge/mysql-%2300f.svg?style=for-the-badge&logo=mysql&logoColor=white)
![PHPMailer](https://img.shields.io/badge/phpmailer-%23AD0769.svg?style=for-the-badge&logo=php&logoColor=white)
