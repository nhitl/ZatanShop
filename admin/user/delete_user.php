<?php
session_start();
include_once '../dbconnect.php';

// Kiểm tra nếu user_id được truyền vào
if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);

    // Kiểm tra user_id có tồn tại trong bảng users không
    $check_user = "SELECT 1 FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $check_user);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 0) {
        $_SESSION['error_message'] = "Người dùng không tồn tại.";
    } else {
        // Kiểm tra xem user có đơn hàng nào không bằng EXISTS
        $check_orders = "SELECT 1 FROM orders WHERE user_id = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $check_orders);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $_SESSION['error_message'] = "Không thể xóa người dùng vì tài khoản này đang có đơn hàng.";
        } else {
            // Xóa user nếu không có đơn hàng
            $delete_query = "DELETE FROM users WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Người dùng đã được xóa thành công.";
            } else {
                $_SESSION['error_message'] = "Lỗi khi xóa người dùng.";
            }
        }
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} else {
    $_SESSION['error_message'] = "ID người dùng không hợp lệ.";
}

// Quay lại trang danh sách người dùng
header("Location: index.php");
exit();
?>
