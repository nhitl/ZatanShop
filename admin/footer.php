<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .footer-custom {
            background-color: #cfcfcf; /* Màu xám đậm hơn */
            color: black; /* Để chữ có màu trắng cho dễ đọc */
        }
    </style>
</head>
<body>
    <!-- Nội dung trang -->
    
    <!-- Footer -->
    <footer class="footer-custom text-center text-lg-start mt-5">
        <div class="text-center p-3 bg-secondary text-white">
            ADMIN ZATAN SHOP
        </div>
        <div class="container p-4">
            <div class="row">
                <!-- Cột 1: Bản quyền -->
                <div class="col-lg-6 col-md-12 mb-4 mb-md-0">
                    <h5 class="text-uppercase">Quản trị hệ thống</h5>
                    <p>&copy; <?php echo date("Y"); ?>. Tất cả các quyền được bảo lưu.</p>
                </div>
                
                <!-- Cột 3: Liên hệ -->
                <div class="col-lg-3 col-md-12 mb-4 mb-md-0">
                    <h5 class="text-uppercase">Liên hệ</h5>
                    <ul class="list-unstyled">
                        <li>Email hỗ trợ: <a href="mailto:nhitran071202@gmail.com" class="text-dark">nhitran071202@gmail.com</a></li>
                        <li>Điện thoại: +84 364 313 062</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Dòng dưới cùng -->
        <div class="text-center p-3 bg-secondary text-white">
            Thiết kế bởi Trần Linh Nhi
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
