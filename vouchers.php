<?php
include_once 'dbconnect.php';

// Lấy `user_id` của người dùng hiện tại
session_start();
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("Bạn cần đăng nhập để xem trang này.");
}

// Lấy danh sách các voucher khả dụng
$sql_vouchers = "
    SELECT voucher_id, voucher_code, description, discount_percentage, expiry_date, min_order_value, max_discount_value
    FROM vouchers
    WHERE status = 'active' AND expiry_date >= CURDATE()
";
$result_vouchers = $conn->query($sql_vouchers);

// Kiểm tra lỗi truy vấn
if (!$result_vouchers) {
    die("Lỗi truy vấn: " . $conn->error);
}

// Lấy danh sách mã voucher đã sử dụng của người dùng
$sql_used_vouchers = "
    SELECT voucher_code
    FROM orders
    WHERE user_id = ? AND voucher_code IS NOT NULL
";
$stmt = $conn->prepare($sql_used_vouchers);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_used_vouchers = $stmt->get_result();

// Chuyển các mã voucher đã sử dụng thành mảng
$used_vouchers = [];
while ($row = $result_used_vouchers->fetch_assoc()) {
    $codes = explode(',', $row['voucher_code']); // Tách chuỗi mã voucher thành mảng
    $used_vouchers = array_merge($used_vouchers, $codes); // Gộp vào mảng `used_vouchers`
}

// Loại bỏ khoảng trắng thừa trong các mã voucher
$used_vouchers = array_map('trim', $used_vouchers);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kho Voucher</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/vouchers.css">
</head>

<body>
    <?php include 'header.php';
    include_once 'contact_button.php'; ?>
    <section class="vouchers">
        <div class="container mt-3">
            <h1 class="text-center mb-4">Kho Voucher</h1>
            <div class="row">
                <?php if ($result_vouchers->num_rows > 0): ?>
                    <?php while ($voucher = $result_vouchers->fetch_assoc()): ?>
                        <?php
                        // Kiểm tra voucher đã sử dụng hay chưa
                        $is_used = in_array($voucher['voucher_code'], $used_vouchers);
                        ?>
                        <div class="col-md-4 col-sm-6 mb-4">
                            <div class="card <?php echo $is_used ? 'border-danger' : 'border-success'; ?> h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($voucher['voucher_code']); ?></h5>
                                    <p class="card-text">
                                        <?php echo htmlspecialchars($voucher['description']); ?>
                                    </p>
                                    <ul>
                                        <li><strong>Giảm giá:</strong> <?php echo $voucher['discount_percentage']; ?>%</li>
                                        <li><strong>HSD:</strong> <?php echo $voucher['expiry_date']; ?></li>
                                        <li><strong>Đơn tối thiểu:</strong> <?php echo number_format($voucher['min_order_value'], 0); ?> VNĐ</li>
                                        <li><strong>Giảm tối đa:</strong> <?php echo number_format($voucher['max_discount_value'], 0); ?> VNĐ</li>
                                    </ul>
                                    <button class="btn <?php echo $is_used ? 'btn-danger' : 'btn-success'; ?> btn-static w-100">
                                        <?php echo $is_used ? 'Đã sử dụng' : 'Chưa sử dụng'; ?>
                                    </button>

                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-center">Không có voucher khả dụng.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>

</html>

<?php
// Đóng kết nối
$conn->close();
?>