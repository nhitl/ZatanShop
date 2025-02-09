<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css?v=2.8">
    <title>Document</title>
</head>

<body>
    <?php
    include_once 'dbconnect.php';
    ?>

    <section class="myfooter">
        <div class="container">
            <div class="row footer-head py-2 px-5">
                <div class="col-6 d-flex justify-content-center align-items-center text-warning">
                    <div class="text-center">
                        <h4>Zatan Shop</h4>
                        <h5>Uy tín tạo niềm tin!</h5>
                    </div>
                </div>

                <div class="col-3 text-warning">
                    <?php
                    // Truy vấn tổng số lượng bán từ cột sales_count
                    $sql = "SELECT SUM(sales_count) as total_sales FROM products";
                    $result = $conn->query($sql);

                    $total_sales = 0;

                    if ($result->num_rows > 0) {
                        // Lấy kết quả
                        $row = $result->fetch_assoc();
                        $total_sales = $row['total_sales'];
                    }
                    ?>
                    <h5>
                    <i class="fa-solid fa-chart-line"></i>
                    <?php echo $total_sales; ?>
                    <p class="m-0">Sản phẩm đã bán</p>
                    </h5>
                    
                </div>
                <div class="col-3 text-warning">
                    <?php
                    // Truy vấn tổng số lượng bán từ cột sales_count
                    $sql = "SELECT SUM(sales_count) as total_sales FROM products";
                    $result = $conn->query($sql);

                    $total_sales = 0;

                    if ($result->num_rows > 0) {
                        // Lấy kết quả
                        $row = $result->fetch_assoc();
                        $total_sales = $row['total_sales'];
                    }
                    
                    ?>
                    <h5>
                    <i class="fa-solid fa-users-line"></i>
                    <?php 
                    echo $total_sales;
                     
                    ?>
                    <p class="m-0">Đang Online</p>
                    </h5>
                    
                </div>
            </div>

            <div class="row footer-body py-3 px-5 text-white">
                <div class="col-sm-6 col-md-4">
                    <h4><img src="admin/assets/img/logoshop1.png" alt="Logo Zatan Shop"></h4>
                    <ul class="list-footer">
                        <li class="li-footer">Địa chỉ : Bắc Giang</li>
                        <li class="li-footer">Số điện thoại: 0364 313 062</li>
                        <li class="li-footer">Email: nhitran071202@gmail.com</li>
                    </ul>

                </div>
                <div class="col-sm-6 col-md-4">
                    <h4>Chính sách</h4>
                    <ul class="list-footer">
                        <li class="li-footer"><a href="/chinh-sach" title="Giới thiệu">Chính sách buôn bán</a></li>
                    </ul>
                </div>
                <div class="col-sm-6 col-md-4">
                    <h4>Tổng đài hỗ trợ</h4>
                    <ul class="list-footer">
                        <li class="li-footer">Gọi mua hàng: 0364 313 062 </li>
                        <li class="li-footer">Gọi bảo hành: 0364 313 062 </li>
                        <li class="li-footer">Gọi khiếu nại: 0364 313 062 </li>
                    </ul>
                    <h4>Phương thức thanh toán</h4>
                    <h5><img src="admin/assets/img/vnpay.png" alt="VnPay"></h5>
                </div>
            </div>
            <div class="row footer-footer py-3 px-5">
                <div class="col text-white">
                    Công ty cổ phần ABC <br>
                    © Bản quyền thuộc về Trần Linh Nhi

                </div>
            </div>
        </div>
        </div>
    </section>


</body>

</html>