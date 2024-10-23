<?php
session_start(); // Khởi động phiên
include_once 'dbconnect.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Bạn cần đăng nhập để đổi mật khẩu.";
    header("Location: login.php"); // Chuyển hướng đến trang đăng nhập
    exit();
}

// Lấy user_id từ phiên
$user_id = $_SESSION['user_id'];

// Xử lý khi form được submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Kiểm tra mật khẩu hiện tại
    $sql = "SELECT password FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($current_password, $row['password'])) {
            // Kiểm tra mật khẩu mới và xác nhận mật khẩu có trùng khớp không
            if (strlen($new_password) < 6) {
                $_SESSION['error'] = "Mật khẩu mới phải có ít nhất 6 ký tự.";
                header("Location: change-password.php");
                exit();
            } elseif ($new_password == $confirm_password) {
                // Mã hóa mật khẩu mới
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Cập nhật mật khẩu mới vào cơ sở dữ liệu
                $update_sql = "UPDATE users SET password = ? WHERE user_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("si", $hashed_password, $user_id);
                if ($update_stmt->execute()) {
                    $_SESSION['success'] = "Đổi mật khẩu thành công!";
                    header("Location: change-password.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Có lỗi xảy ra khi cập nhật mật khẩu.";
                    header("Location: change-password.php");
                    exit();
                }
                $update_stmt->close();
            } else {
                $_SESSION['error'] = "Mật khẩu mới và xác nhận mật khẩu không khớp.";
                header("Location: change-password.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Mật khẩu hiện tại không chính xác.";
            header("Location: change-password.php");
            exit();
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi Mật Khẩu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styleinfo.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <section class="change-pw">
        <div class="container mt-5">
            <!-- Hiển thị thông báo lỗi hoặc thành công -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Form Đổi Mật Khẩu -->
            <!-- Card Form Đổi Mật Khẩu -->
            <div class="card shadow-sm my-5" style="max-width: 500px; margin: 0 auto;">
                <div class="card-header">
                    Đổi Mật Khẩu
                </div>
                <div class="card-body">
                    <!-- Form Đổi Mật Khẩu -->
                    <form method="POST" action="" class="row g-3">
                        <div class="col-12">
                            <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="col-12">
                            <label for="new_password" class="form-label">Mật khẩu mới</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        <div class="col-12">
                            <label for="confirm_password" class="form-label">Xác nhận mật khẩu mới</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary w-100">Đổi Mật Khẩu</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </section>
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>