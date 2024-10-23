<?php

// Kết nối CSDL
include_once 'dbconnect.php';

// Truy vấn CSDL để lấy số lượng sản phẩm
$productQuery = "SELECT COUNT(*) as product_count FROM products";
$productResult = mysqli_query($conn, $productQuery);
$productCount = mysqli_fetch_assoc($productResult)['product_count'];

// Truy vấn CSDL để lấy số lượng đơn hàng
$orderQuery = "SELECT COUNT(*) as order_count FROM orders";
$orderResult = mysqli_query($conn, $orderQuery);


// Truy vấn CSDL để lấy số lượng người dùng
$userQuery = "SELECT COUNT(*) as user_count FROM users";
$userResult = mysqli_query($conn, $userQuery);
$userCount = mysqli_fetch_assoc($userResult)['user_count'];

// Truy vấn CSDL để lấy số lượng tin tức
$newsQuery = "SELECT COUNT(*) as news_count FROM news";
$newsResult = mysqli_query($conn, $newsQuery);
$newsCount = mysqli_fetch_assoc($newsResult)['news_count'];

// Đóng kết nối CSDL
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <link rel="stylesheet" href="assets\css\modal.css">
    <style>
        .container-fluid{
            margin-top: 80px;
        }
    </style>
    <!-- Thêm thư viện Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <?php
    // Include header
    include_once 'header.php';
    ?>

    <!-- Nội dung -->
    <div class="container-fluid">
        <div class="row">
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
                <h2 class="my-4">Dashboard</h2>

                <!-- Hiển thị thông tin -->
                <div class="row">
                    <?php
                    // Thông tin sản phẩm
                    echo '<div class="col-md-3">';
                    echo '<div class="card bg-primary text-white">';
                    echo '<div class="card-body">';
                    echo '<i class="fas fa-cube fa-2x"></i>';
                    echo '<h5 class="card-title">Số lượng sản phẩm</h5>';
                    echo '<p class="card-text">Đang có: ' . $productCount . '</p>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';

                    // Thông tin đơn hàng
                    echo '<div class="col-md-3">';
                    echo '<div class="card bg-success text-white">';
                    echo '<div class="card-body">';
                    echo '<i class="fas fa-shopping-cart fa-2x"></i>';
                    echo '<h5 class="card-title">Số lượng đơn hàng</h5>';
                    echo '<p class="card-text">Đã đặt: ' . $orderCount . '</p>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';

                    // Thông tin người dùng
                    echo '<div class="col-md-3">';
                    echo '<div class="card bg-danger text-white">';
                    echo '<div class="card-body">';
                    echo '<i class="fas fa-users fa-2x"></i>';
                    echo '<h5 class="card-title">Số lượng người dùng</h5>';
                    echo '<p class="card-text">Tổng số: ' . $userCount . '</p>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';

                    // Thông tin tin tức
                    echo '<div class="col-md-3">';
                    echo '<div class="card bg-info text-white">';
                    echo '<div class="card-body">';
                    echo '<i class="far fa-newspaper fa-2x"></i>';
                    echo '<h5 class="card-title">Tin Công Nghệ</h5>';
                    echo '<p class="card-text">Hiện có: ' . $newsCount . '</p>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    ?>
                </div>

                <!-- Đồ thị doanh thu -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Doanh thu năm 2023</h5>
                                <canvas id="revenueChart" width="400" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script>
        // Dữ liệu doanh thu (số ngẫu nhiên)
        var revenueData = [2000, 3000, 4500, 6000, 8000, 10000, 12000, 15000, 18000, 20000, 22000, 25000];

        // Lấy thẻ canvas
        var ctx = document.getElementById('revenueChart').getContext('2d');

        // Tạo đồ thị
        var revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
                datasets: [{
                    label: 'Doanh thu (VND)',
                    data: revenueData,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    fill: false
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>