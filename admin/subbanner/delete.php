<?php
// Kết nối SDL
include_once '../dbconnect.php';

// Kiểm tra xem có id không
if (isset($_GET['id'])) {
    $subbanner_id = intval($_GET['id']);

    // Truy vấn để xóa subbanner
    $sql = "DELETE FROM subbanners WHERE subbanner_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $subbanner_id);

    if ($stmt->execute()) {
        // Nếu xóa thành công, lưu thông báo vào session
        $_SESSION['success_message'] = "Subbanner đã được xóa thành công!";
    } else {
        // Nếu xóa không thành công, lưu thông báo lỗi vào session
        $_SESSION['error_message'] = "Có lỗi xảy ra trong quá trình xóa subbanner.";
    }

    // Đóng kết nối
    $stmt->close();
}

// Chuyển hướng về trang danh sách subbanners
header("Location: index.php");
exit();
?>
