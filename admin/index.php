<?php
// Kết nối CSDL
include_once 'dbconnect.php';

// Truy vấn CSDL để lấy số lượng sản phẩm
$productQuery = "SELECT COUNT(*) as product_count FROM products";
$productResult = mysqli_query($conn, $productQuery);
$productCount = $productResult ? mysqli_fetch_assoc($productResult)['product_count'] : 0;

// Truy vấn CSDL để lấy số lượng đơn hàng
$orderQuery = "SELECT COUNT(*) as order_count FROM orders";
$orderResult = mysqli_query($conn, $orderQuery);
$orderCount = $orderResult ? mysqli_fetch_assoc($orderResult)['order_count'] : 0;

// Truy vấn CSDL để lấy số lượng người dùng
$userQuery = "SELECT COUNT(*) as user_count FROM users";
$userResult = mysqli_query($conn, $userQuery);
$userCount = $userResult ? mysqli_fetch_assoc($userResult)['user_count'] : 0;

// Truy vấn CSDL để lấy số lượng tin tức
$newsQuery = "SELECT COUNT(*) as news_count FROM news";
$newsResult = mysqli_query($conn, $newsQuery);
$newsCount = $newsResult ? mysqli_fetch_assoc($newsResult)['news_count'] : 0;

// Lấy tổng doanh thu từ grand_total cho từng tháng trong năm hiện tại
$currentYear = date('Y');
$query = "SELECT MONTH(created_at) AS month, SUM(grand_total) AS total_revenue
          FROM orders
          WHERE YEAR(created_at) = $currentYear
          GROUP BY MONTH(created_at)";

$result = mysqli_query($conn, $query);

// Tạo mảng doanh thu mặc định là 0 cho tất cả các tháng
$monthlyRevenue = array_fill(1, 12, 0);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $month = (int) $row['month'];
        $monthlyRevenue[$month] = (float) $row['total_revenue'];
    }
}

// Truy vấn số lượng đơn hàng theo trạng thái
$sql = "SELECT order_status, COUNT(*) as count FROM orders GROUP BY order_status";
$result = $conn->query($sql);

$orderStatusCounts = [];
$labels = [];

// Định nghĩa bản dịch cho trạng thái đơn hàng
$statusTranslation = [
    'pending' => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'shipping' => 'Đang giao',
    'delivered' => 'Đã giao',
    'canceled' => 'Đã hủy'
];

if ($result) { // Kiểm tra xem truy vấn có thành công không
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Chuyển đổi trạng thái sang tiếng Việt
            $statusVietnamese = $statusTranslation[$row['order_status']];
            $labels[] = $statusVietnamese;
            $orderStatusCounts[] = (int)$row['count'];
        }
    }
} else {
    // In ra lỗi truy vấn nếu có
    echo "Lỗi truy vấn đơn hàng: " . $conn->error;
}

// Truy vấn số lượng người dùng theo tháng
$sqlUsers = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS user_count 
             FROM users 
             GROUP BY month 
             ORDER BY month";
$resultUsers = $conn->query($sqlUsers);

$userCounts = [];
$months = [];

if ($resultUsers) { // Kiểm tra xem truy vấn có thành công không
    if ($resultUsers->num_rows > 0) {
        while ($row = $resultUsers->fetch_assoc()) {
            $months[] = $row['month']; // Tháng
            $userCounts[] = (int)$row['user_count']; // Số lượng người dùng
        }
    }
} else {
    // In ra lỗi truy vấn nếu có
    echo "Lỗi truy vấn người dùng: " . $conn->error;
}

// Truy vấn dữ liệu
$sql = "SELECT c.category_name, SUM(oi.quantity) AS total_quantity
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        JOIN categories c ON p.category_id = c.category_id
        GROUP BY c.category_id";
$categoryResult = $conn->query($sql);

$categoryNames = [];
$quantities = [];

if ($categoryResult->num_rows > 0) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categoryNames[] = $row['category_name'];
        $quantities[] = (int)$row['total_quantity'];
    }
} else {
    echo "Không có dữ liệu.";
}


// Đóng kết nối
$conn->close();

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-card {
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .dashboard-card:hover {
            transform: scale(1.05);
        }

        .chart-container {
            margin-top: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
        }

        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: white;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <?php
    include_once 'header.php';
    ?>
    <div class="container mb-5">
        <div class="row">
            <main role="main" class="col-lg-12 px-4">
                <h2 class="my-4 text-center">Dashboard</h2>

                <!-- Thẻ thống kê -->
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white dashboard-card">
                            <div class="card-body text-center">
                                <i class="fas fa-cube card-icon"></i>
                                <h5 class="card-title">Sản phẩm</h5>
                                <p class="card-text">Đang có: <?= $productCount ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card bg-success text-white dashboard-card">
                            <div class="card-body text-center">
                                <i class="fas fa-shopping-cart card-icon"></i>
                                <h5 class="card-title">Đơn hàng</h5>
                                <p class="card-text">Đã đặt: <?= $orderCount ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card bg-danger text-white dashboard-card">
                            <div class="card-body text-center">
                                <i class="fas fa-users card-icon"></i>
                                <h5 class="card-title">Người dùng</h5>
                                <p class="card-text">Tổng số: <?= $userCount ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card bg-info text-white dashboard-card">
                            <div class="card-body text-center">
                                <i class="far fa-newspaper card-icon"></i>
                                <h5 class="card-title">Tin Công Nghệ</h5>
                                <p class="card-text">Hiện có: <?= $newsCount ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Đồ thị doanh thu -->
                <div class="chart-container bg-white mt-5 p-4 rounded">
                    <h5 class="text-center mb-4">Doanh thu năm 2025</h5>
                    <canvas id="revenueChart"></canvas>
                </div>

                <div class="container mt-5">
                    <div class="row">
                        <!-- Đồ thị trạng thái đơn hàng -->
                        <div class="col-md-6">
                            <div class="chart-container bg-white p-4 rounded">
                                <h5 class="text-center mb-4">Đơn hàng theo trạng thái</h5>
                                <canvas id="orderStatusChart"></canvas>
                            </div>
                        </div>
                        <!-- Đồ thị tăng trưởng khách hàng -->
                        <div class="col-md-6">
                            <div class="chart-container bg-white p-4 rounded">
                                <h5 class="text-center mb-4">Biểu đồ tăng trưởng số lượng người dùng mới từng tháng</h5>
                                <canvas id="customerGrowthChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Phần tử canvas cho biểu đồ tròn -->
                <div class="chart-container bg-white mt-5 p-4 rounded">
                    <h5 class="text-center mb-4">Biểu đồ sản phẩm đã bán theo loại</h5>
                    <canvas id="categoryChart" width="400" height="400"></canvas>
                </div>
            </main>
        </div>
    </div>
    <?php
    include_once 'footer.php';
    ?>
    <script>
        // Nhận dữ liệu từ PHP vào JavaScript
        var revenueData = <?php echo json_encode(array_values($monthlyRevenue)); ?>;

        // Vẽ biểu đồ
        var ctx = document.getElementById('revenueChart').getContext('2d');
        var revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
                datasets: [{
                    label: 'Doanh thu (VND)',
                    data: revenueData,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#333'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#333'
                        }
                    }
                }
            }
        });
    </script>
    <script>
        var ctx = document.getElementById('orderStatusChart').getContext('2d');

        var orderStatusCounts = <?php echo json_encode($orderStatusCounts); ?>;
        var labels = <?php echo json_encode($labels); ?>;

        var orderStatusChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Số đơn',
                    data: orderStatusCounts,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Biểu đồ Đơn hàng theo Trạng thái'
                    }
                }
            }
        });
    </script>
    <script>
        var ctx = document.getElementById('customerGrowthChart').getContext('2d');
        var customerGrowthData = <?php echo json_encode($userCounts); ?>; // Số lượng khách hàng
        var labels = <?php echo json_encode($months); ?>; // Tháng

        var customerGrowthChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Số lượng khách hàng',
                    data: customerGrowthData,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#333'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#333'
                        }
                    }
                }
            }
        });
    </script>
    <script>
        // Xuất dữ liệu từ PHP sang JavaScript
        const categoryNames = <?php echo json_encode($categoryNames); ?>;
        const quantities = <?php echo json_encode($quantities); ?>;



        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('categoryChart').getContext('2d');

            // Khởi tạo biểu đồ
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: categoryNames,
                    datasets: [{
                        label: 'Sản phẩm bán chạy theo loại',
                        data: quantities,
                        backgroundColor: [
                            'rgba(86, 216, 103, 0.6)',
                            'rgba(54, 162, 235, 0.6)',
                            'rgba(255, 206, 86, 0.6)',
                            'rgba(75, 192, 192, 0.6)',
                            'rgba(153, 102, 255, 0.6)',
                            'rgba(255, 159, 64, 0.6)'
                        ],
                        borderColor: [
                            'rgb(56, 182, 104)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Đã bán' + ': ' + context.raw + ' sản phẩm';
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>

</body>

</html>