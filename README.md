# Website Quản lý chi tiêu cá nhân (PHP + MySQL)

## 1. Chức năng
- Tổng quan số dư, tổng thu, tổng chi theo tháng.
- Biểu đồ thu/chi theo ngày và biểu đồ chi tiêu theo danh mục.
- Thêm, xem, sửa, xóa giao dịch thu/chi.
- Quản lý danh mục thu nhập và chi tiêu.
- Lọc giao dịch theo tháng, loại, danh mục; tìm kiếm theo tên hoặc ghi chú.
- Giao diện tiếng Việt, responsive cho máy tính và điện thoại.
- Sử dụng PDO prepared statements, escape dữ liệu HTML và CSRF token cho thao tác POST.

## 2. Yêu cầu
- XAMPP (Apache + MySQL/MariaDB) hoặc môi trường PHP/MySQL tương đương.
- PHP 8.0 trở lên được khuyến nghị.
- Trình duyệt web hiện đại.
- Kết nối Internet để tải Chart.js và font chữ từ CDN. Nếu không có Internet, các phần PHP vẫn hoạt động nhưng biểu đồ/thay đổi font có thể không hiển thị như mong muốn.

## 3. Cài đặt với XAMPP
1. Giải nén thư mục `chi_tieu_ca_nhan` vào `C:\xampp\htdocs\`.
2. Mở XAMPP Control Panel, nhấn **Start** ở Apache và MySQL.
3. Mở `http://localhost/phpmyadmin`.
4. Chọn tab **Import** → chọn tệp `database.sql` → nhấn **Import** hoặc **Go**.
   - Tệp SQL tự tạo database `quan_ly_chi_tieu` và các bảng cần thiết.
   - Nếu dùng tài khoản MySQL có mật khẩu, sửa cấu hình trong `config.php`.
5. Mở `config.php` và kiểm tra:
   - `$DB_HOST = 'localhost';`
   - `$DB_NAME = 'quan_ly_chi_tieu';`
   - `$DB_USER = 'root';`
   - `$DB_PASS = '';` (mặc định thường gặp trong XAMPP; sửa nếu bạn đặt mật khẩu).
6. Mở website: `http://localhost/chi_tieu_ca_nhan/`.

## 4. Cách sử dụng
1. Vào **Danh mục** để xem danh mục mẫu; có thể thêm, sửa hoặc xóa danh mục.
2. Chọn **Thêm giao dịch** để ghi khoản thu hoặc chi.
3. Chọn đúng loại giao dịch rồi chọn danh mục cùng loại.
4. Vào **Giao dịch** để sửa, xóa, tìm kiếm hoặc lọc theo tháng.
5. Vào **Tổng quan** để xem số dư và biểu đồ theo tháng đã chọn.

## 5. Cấu trúc thư mục
```
chi_tieu_ca_nhan/
├── assets/
│   └── style.css
├── config.php
├── helpers.php
├── header.php
├── footer.php
├── index.php
├── transactions.php
├── transaction_form.php
├── transaction_delete.php
├── categories.php
├── database.sql
└── README.md
```

## 6. Lỗi thường gặp
- **Access denied for user**: sửa user/password MySQL trong `config.php`.
- **Unknown database `quan_ly_chi_tieu`**: import lại `database.sql` trong phpMyAdmin.
- **Không thể kết nối MySQL**: kiểm tra MySQL đã Start trong XAMPP chưa.
- **Trang PHP hiện nguyên mã nguồn**: không mở tệp bằng cách nhấp đúp; phải chạy Apache và truy cập qua `http://localhost/...`.
- **Biểu đồ không hiện**: kiểm tra Internet/CDN và Console của trình duyệt.

## 7. Lưu ý
- Bản mẫu này chạy cục bộ, chưa có đăng nhập/tài khoản người dùng; dữ liệu trong database dùng chung cho người truy cập ứng dụng trên máy chủ đó.
- Nếu triển khai công khai trên Internet, cần bổ sung xác thực, phân quyền, bảo vệ cấu hình, HTTPS, giới hạn truy cập và sao lưu dữ liệu.
- Trước khi xóa danh mục, cần xử lý các giao dịch đang tham chiếu danh mục đó.
