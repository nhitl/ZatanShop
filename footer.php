<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php
    include_once 'dbconnect.php';
    ?>

    <section class="myfooter">
        <div class="container">
            <div class="row footer-head py-2 px-5">
                <div class="col-6">
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
                <div class="col-sm-6 col-md-3">
                    <h4><img src="admin/assets/img/logoshop1.png" alt=""></h4>
                    <ul class="list-footer">
                        <li class="li-footer">Địa chỉ</li>
                        <li class="li-footer">Số điện thoại</li>
                        <li class="li-footer">Email</li>
                    </ul>

                </div>
                <div class="col-sm-6 col-md-3">
                    <h4>Hỗ trợ khách hàng</h4>
                    <ul class="list-footer">
                        <li class="li-footer"><a href="/gioi-thieu.php" title="Giới thiệu">Giới thiệu</a></li>
                        <li class="li-footer"><a href="/gioi-thieu.php" title="Giới thiệu">Liên hệ</a></li>
                        <li class="li-footer"><a href="/gioi-thieu.php" title="Giới thiệu">Hướng dẫn trả góp</a></li>
                        <li class="li-footer"><a href="/gioi-thieu.php" title="Giới thiệu">Hướng đẫn mua hàng Online</a></li>
                        <li class="li-footer"><a href="/gioi-thieu.php" title="Giới thiệu">Câu hỏi thường gặp</a></li>
                    </ul>
                </div>
                <div class="col-sm-6 col-md-3">
                    <h4>Chính sách</h4>
                    <ul class="list-footer">
                        <li class="li-footer"><a href="/gioi-thieu.php" title="Giới thiệu">Chính sách bảo mật</a></li>
                        <li class="li-footer"><a href="/gioi-thieu.php" title="Giới thiệu">Chính sách đổi trả</a></li>
                        <li class="li-footer"><a href="/gioi-thieu.php" title="Giới thiệu">Chính sách bảo hành</a></li>
                        <li class="li-footer"><a href="/gioi-thieu.php" title="Giới thiệu">Chính sách đặt cọc giữ hàng</a></li>
                    </ul>
                </div>
                <div class="col-sm-6 col-md-3">
                    <h4>Tổng đài hỗ trợ</h4>
                    <ul class="list-footer">
                        <li class="li-footer">Gọi mua hàng: 19006750 </li>
                        <li class="li-footer">Gọi bảo hành: 19006750 </li>
                        <li class="li-footer">Gọi khiếu nại: 19006750 </li>
                    </ul>
                    <h4>Phương thức thanh toán</h4>
                    <h5>ảnh</h5>
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