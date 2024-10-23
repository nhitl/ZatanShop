<?php
// Bắt đầu phiên làm việc
session_start();

// Kết nối CSDL
include_once '../dbconnect.php';

// Kiểm tra xem có ID không
if (isset($_GET['id'])) {
    $banner_id = intval($_GET['id']);

    // Truy vấn để xóa banner
    $sql = "DELETE FROM banners WHERE banner_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $banner_id);

    if ($stmt->execute()) {
        // Nếu xóa thành công, lưu thông báo vào session
        $_SESSION['success_message'] = "Banner đã được xóa thành công!";
    } else {
        // Nếu xóa không thành công, lưu thông báo lỗi vào session
        $_SESSION['error_message'] = "Có lỗi xảy ra trong quá trình xóa banner.";
    }

    // Đóng kết nối
    $stmt->close();
} else {
    $_SESSION['error_message'] = "Không có ID banner được cung cấp!";
}

// Chuyển hướng về trang danh sách banners
header("Location: index.php");
exit();
?>
