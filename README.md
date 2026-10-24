# Đề tài Thực hành: Website Quảng bá Sản phẩm (WordPress)
Mã số SV: dtc245010043

## Hướng dẫn chạy
```bash
docker compose up -d
```

Sau khi khởi động thành công:
- Website: http://localhost:8088
- phpMyAdmin: http://localhost:8080
- Prometheus: http://localhost:9090
- Grafana: http://localhost:3000

Lưu ý: cổng 80 trên máy đang bị chiếm bởi dịch vụ IIS của Windows, nên proxy Nginx được cấu hình để chạy trên cổng 8088 để tránh xung đột.

## Giao diện và cửa hàng
- Theme: Astra
- Plugin: WooCommerce, Spectra (khối giao diện Gutenberg), Yoast SEO
- Nhận diện: ICTU Tech Store, tông xanh ICTU và logo do người dùng cung cấp
- Nội dung ghi rõ đây là website demo cho đồ án sinh viên, không phải website chính thức của ICTU
- Tiền tệ cửa hàng: VND
- Trang chủ: landing page giới thiệu sản phẩm
- Trang cửa hàng: http://localhost:8088/shop/
- Trang giới thiệu: http://localhost:8088/ve-chung-toi/
- Hướng dẫn mua hàng: http://localhost:8088/huong-dan-mua-hang/
- Câu hỏi thường gặp: http://localhost:8088/cau-hoi-thuong-gap/
- Liên hệ: http://localhost:8088/lien-he/
- Chính sách: điều khoản sử dụng, giao nhận, đổi trả và bảo mật
- Menu điều hướng hỗ trợ desktop, mobile và footer.
- Có sẵn 8 sản phẩm mẫu để kiểm tra trang chủ và chức năng cửa hàng; giá và tồn kho là dữ liệu demo.
- Thanh toán chuyển khoản BACS hiển thị QR VietQR theo số tiền và mã tham chiếu; QR đơn hàng cũng xuất hiện sau khi đặt đơn.
- QR được tải từ dịch vụ VietQR bên ngoài và chứa thông tin cần thiết để tạo lệnh chuyển khoản. Thanh toán không được xác nhận tự động; cửa hàng phải đối soát thủ công.
- Không dùng dữ liệu cá nhân hoặc thanh toán thật trên bản demo. Trước khi bán hàng, cần bổ sung thông tin liên hệ, địa chỉ nhận hàng, vận chuyển và chính sách thực tế.
