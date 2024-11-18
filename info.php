<?php
session_start(); // Khởi động phiên
include_once 'dbconnect.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    echo "Bạn cần đăng nhập để xem thông tin.";
    exit();
}

// Lấy user_id từ phiên
$user_id = $_SESSION['user_id'];

// Truy vấn để lấy thông tin người dùng
$sql = "SELECT full_name, email FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Kiểm tra xem có dữ liệu không
if ($result->num_rows > 0) {
    // Lấy dữ liệu của người dùng
    $row = $result->fetch_assoc();
    $full_name = $row['full_name'];
    $email = $row['email'];
} else {
    $full_name = "Không có thông tin";
    $email = "Không có thông tin";
}

$stmt->close();

// Xử lý cập nhật tên
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_full_name']) && !empty($_POST['new_full_name'])) {
    $new_full_name = $_POST['new_full_name'];

    // Cập nhật tên mới vào cơ sở dữ liệu
    $update_sql = "UPDATE users SET full_name = ? WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $new_full_name, $user_id);
    $update_stmt->execute();

    // Cập nhật lại tên trong phiên
    $_SESSION['full_name'] = $new_full_name;

    // Chuyển hướng về chính trang sau khi cập nhật tên
    header("Location: " . $_SERVER['PHP_SELF']); // Chuyển hướng về trang này
    exit(); // Dừng lại sau khi chuyển hướng để không chạy tiếp mã phía dưới
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông Tin Người Dùng</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="assets/css/styleinfo.css">
</head>

<body>
    <?php include 'header.php';
    include_once 'contact_button.php'; ?>

    <section class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Thông tin</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="info-user">
        <main class="container mt-5">
            <div class="row">
                <!-- Phần Ảnh Đại Diện và Thông Tin Người Dùng -->
                <div class="col-md-4 d-flex justify-content-center align-items-start mb-4">
                    <div class="text-center">
                        <!-- Ảnh đại diện -->
                        <img src="assets/img/imgusers.png" alt="Ảnh Đại Diện" class="img-fluid rounded-circle" style="width: 120px; height: 120px;">
                    </div>
                </div>
                <div class="col-md-4 col-8 center-form">
                    <div>
                        <h2>Tên:</h2>
                        <form method="POST" action="">
                            <div class="input-group">
                                <input type="text" class="form-control" name="new_full_name" value="<?php echo htmlspecialchars($full_name); ?>" required>
                                <button type="submit" class="btn btn-success"><i class="fa-solid fa-arrows-rotate"></i></button>
                            </div>
                        </form>
                    </div>
                </div>


            </div>

            <div class="row mt-4">
                <div class="col-md-3 col-6 d-flex justify-content-center">
                    <a href="history-orders.php" class="btn btn-secondary">Theo giõi đơn hàng</a>
                </div>
                <div class="col-md-3 col-6 d-flex justify-content-center">
                    <a href="setting-address.php" class="btn btn-secondary">Thiết lập địa chỉ</a>
                </div>
                <div class="col-md-3 col-6 d-flex justify-content-center">
                    <a href="change-password.php" class="btn btn-secondary">Đổi mật khẩu</a>
                </div>
                <div class="col-md-3 col-6 d-flex justify-content-center">
                    <a href="logout.php" class="btn btn-secondary">Đăng xuất</a>
                </div>
            </div>
        </main>
    </section>

    <?php include 'footer.php'; ?>
</body>

</html>