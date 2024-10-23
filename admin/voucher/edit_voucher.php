<?php
session_start();  // Bắt đầu session
include_once '../dbconnect.php';

if (isset($_GET['id'])) {
    $voucher_id = intval($_GET['id']);  // Đảm bảo voucher_id là số nguyên
    $result = $conn->query("SELECT * FROM voucher WHERE voucher_id = $voucher_id");
    if ($result->num_rows > 0) {
        $voucher = $result->fetch_assoc();
    } else {
        $_SESSION['error_message'] = "Voucher không tồn tại!";
        header("Location: index.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voucher_code = $conn->real_escape_string($_POST['voucher_code']);
    $description = $conn->real_escape_string($_POST['description']);
    $discount_percentage = intval($_POST['discount_percentage']);
    $expiry_date = $conn->real_escape_string($_POST['expiry_date']);
    $min_order_value = intval($_POST['min_order_value']);
    $max_discount_value = intval($_POST['max_discount_value']);
    $status = $conn->real_escape_string($_POST['status']);

    // Kiểm tra trùng mã voucher
    $sql_check = "SELECT * FROM voucher WHERE voucher_code='$voucher_code' AND voucher_id != $voucher_id";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        $_SESSION['error_message'] = "Mã voucher đã tồn tại!";
        header("Location: edit_voucher.php?id=$voucher_id");
        exit();  // Đảm bảo không có mã nào sau khi header được thực thi
    } else {
        // Xử lý cập nhật
        $sql = "UPDATE voucher SET voucher_code='$voucher_code', description='$description', discount_percentage='$discount_percentage',
                expiry_date='$expiry_date', min_order_value='$min_order_value', max_discount_value='$max_discount_value', status='$status'
                WHERE voucher_id=$voucher_id";

        if ($conn->query($sql) === TRUE) {
            $_SESSION['success_message'] = "Voucher đã được cập nhật thành công!";
            header("Location: index.php");
            exit();  // Đảm bảo không có mã nào sau khi header được thực thi
        } else {
            $_SESSION['error_message'] = "Lỗi: " . $conn->error;
            header("Location: edit_voucher.php?id=$voucher_id");
            exit();  // Đảm bảo không có mã nào sau khi header được thực thi
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa Voucher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/modal.css">
    <style>
        .container {
            margin-top: 80px;
        }

        .text-danger {
            color: red;
        }
    </style>
</head>

<body>
    <?php
    // Include header
    include_once '../header.php';

    // Hiển thị thông báo lỗi nếu có
    if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?php echo $_SESSION['error_message']; ?>
        </div>
        <?php unset($_SESSION['error_message']); // Xóa thông báo sau khi hiển thị 
    endif;

    // Hiển thị thông báo thành công nếu có
    if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success_message']; ?>
        </div>
        <?php unset($_SESSION['success_message']); // Xóa thông báo sau khi hiển thị 
    endif;
    ?>

    <div class="container">
    <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-left-long"></i></a>

        <h1 class="text-center">Chỉnh sửa Voucher</h1>
        <form action="" method="POST" id="voucher-form">
            <div class="mb-3">
                <label for="voucher_code" class="form-label">Mã Voucher</label>
                <input type="text" class="form-control" id="voucher_code" name="voucher_code" value="<?php echo htmlspecialchars($voucher['voucher_code']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Mô tả</label>
                <textarea class="form-control" id="description" name="description"><?php echo htmlspecialchars($voucher['description']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="discount_percentage" class="form-label">Phần trăm giảm</label>
                <input type="number" class="form-control" id="discount_percentage" name="discount_percentage" value="<?php echo intval($voucher['discount_percentage']); ?>" required>
                <div id="discount_percentage_error" class="text-danger"></div>
            </div>
            <div class="mb-3">
                <label for="expiry_date" class="form-label">Ngày hết hạn</label>
                <input type="date" class="form-control" id="expiry_date" name="expiry_date" value="<?php echo htmlspecialchars($voucher['expiry_date']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="min_order_value" class="form-label">Giá trị đơn tối thiểu</label>
                <input type="number" class="form-control" id="min_order_value" name="min_order_value" value="<?php echo intval($voucher['min_order_value']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="max_discount_value" class="form-label">Giá trị giảm tối đa</label>
                <input type="number" class="form-control" id="max_discount_value" name="max_discount_value" value="<?php echo intval($voucher['max_discount_value']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Trạng thái</label>
                <select class="form-control" id="status" name="status">
                    <option value="active" <?php echo ($voucher['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($voucher['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Cập nhật</button>
        </form>
    </div>

    <script>
        document.getElementById('voucher-form').addEventListener('submit', function(event) {
            var discountPercentage = document.getElementById('discount_percentage').value;
            var errorDiv = document.getElementById('discount_percentage_error');

            if (discountPercentage > 100) {
                errorDiv.textContent = 'Phần trăm giảm không thể vượt quá 100.';
                event.preventDefault(); // Ngăn chặn gửi biểu mẫu
            } else {
                errorDiv.textContent = '';
            }
        });
    </script>
</body>

</html>
