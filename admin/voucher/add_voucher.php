<?php
session_start();  // Bắt đầu session
include_once '../dbconnect.php';

$errors = [];  // Mảng lưu trữ lỗi

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voucher_code = $_POST['voucher_code'];
    $description = $_POST['description'];
    $discount_percentage = $_POST['discount_percentage'];
    $expiry_date = $_POST['expiry_date'];
    $min_order_value = $_POST['min_order_value'];
    $max_discount_value = $_POST['max_discount_value'];
    $status = $_POST['status'];

    // Kiểm tra trùng lặp mã voucher
    $check_sql = "SELECT * FROM voucher WHERE voucher_code = '$voucher_code'";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        $errors[] = "Mã voucher đã tồn tại!";
    }

    // Nếu không có lỗi, thực hiện thêm voucher
    if (empty($errors)) {
        // Kiểm tra nếu phần trăm giảm lớn hơn 100
        if ($discount_percentage > 100) {
            $errors[] = "Phần trăm giảm không được vượt quá 100!";
        }

        if (empty($errors)) {
            $sql = "INSERT INTO voucher (voucher_code, description, discount_percentage, expiry_date, min_order_value, max_discount_value, status)
                    VALUES ('$voucher_code', '$description', '$discount_percentage', '$expiry_date', '$min_order_value', '$max_discount_value', '$status')";

            if ($conn->query($sql) === TRUE) {
                $_SESSION['success_message'] = "Voucher đã được thêm thành công!";
                header("Location: index.php");
                exit();  // Đảm bảo không có mã nào sau khi header được thực thi
            } else {
                $errors[] = "Lỗi: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Voucher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/modal.css">
    <style>
        .container {
            margin-top: 80px;
        }
        .error-message {
            color: red;
            font-size: 0.875rem;
        }
    </style>
</head>

<body>
    <?php
    // Include header
    include_once '../header.php';
    ?>
    <div class="container">
    <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-left-long"></i></a>
        <h1 class="text-center">Thêm Voucher mới</h1>

        <!-- Hiển thị lỗi nếu có -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="" method="POST" id="voucherForm">
            <div class="mb-3">
                <label for="voucher_code" class="form-label">Mã Voucher</label>
                <input type="text" class="form-control" id="voucher_code" name="voucher_code" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Mô tả</label>
                <textarea class="form-control" id="description" name="description"></textarea>
            </div>
            <div class="mb-3">
                <label for="discount_percentage" class="form-label">Phần trăm giảm</label>
                <input type="number" class="form-control" id="discount_percentage" name="discount_percentage" required>
                <div id="discount_percentage_error" class="error-message"></div>
            </div>
            <div class="mb-3">
                <label for="expiry_date" class="form-label">Ngày hết hạn</label>
                <input type="date" class="form-control" id="expiry_date" name="expiry_date" required>
            </div>
            <div class="mb-3">
                <label for="min_order_value" class="form-label">Giá trị đơn tối thiểu</label>
                <input type="number" class="form-control" id="min_order_value" name="min_order_value" required>
            </div>
            <div class="mb-3">
                <label for="max_discount_value" class="form-label">Giá trị giảm tối đa</label>
                <input type="number" class="form-control" id="max_discount_value" name="max_discount_value" required>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Trạng thái</label>
                <select class="form-control" id="status" name="status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Thêm</button>
        </form>

        <script>
            document.getElementById('voucherForm').addEventListener('submit', function(event) {
                var discountPercentage = document.getElementById('discount_percentage').value;
                var discountPercentageError = document.getElementById('discount_percentage_error');

                // Reset lỗi trước khi kiểm tra
                discountPercentageError.textContent = '';

                // Kiểm tra phần trăm giảm
                if (discountPercentage > 100) {
                    discountPercentageError.textContent = 'Phần trăm giảm không được vượt quá 100!';
                    event.preventDefault();  // Ngăn chặn gửi form nếu có lỗi
                }
            });
        </script>
    </div>
</body>

</html>
