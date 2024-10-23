<?php
session_start();  // Bắt đầu session
include_once '../dbconnect.php';

if (isset($_GET['id'])) {
    $voucher_id = $_GET['id'];

    // Thực hiện câu lệnh DELETE
    if ($conn->query("DELETE FROM voucher WHERE voucher_id = $voucher_id") === TRUE) {
        // Nếu xóa thành công, lưu thông báo vào session
        $_SESSION['success_message'] = "Voucher đã được xóa thành công!";
    } else {
        $_SESSION['success_message'] = "Đã xảy ra lỗi khi xóa voucher!";
    }

    // Chuyển hướng về trang danh sách voucher
    header("Location: index.php");
    exit();  // Đảm bảo không có mã nào khác được thực thi sau khi header
}
?>
