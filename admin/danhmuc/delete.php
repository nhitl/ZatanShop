<?php
session_start(); // Khởi động session
include_once '../dbconnect.php';

// Kiểm tra xem có tham số category_id được truyền không
if (isset($_GET['category_id'])) {
    $categoryId = $_GET['category_id'];

    // Xóa danh mục từ bảng categories
    $deleteCategoryQuery = "DELETE FROM categories WHERE category_id = $categoryId";
    
    if (mysqli_query($conn, $deleteCategoryQuery)) {
        // Thiết lập thông báo thành công
        $_SESSION['success_message'] = "Danh mục đã được xóa thành công!";
    } else {
        // Thiết lập thông báo lỗi nếu xóa không thành công
        $_SESSION['success_message'] = "Đã xảy ra lỗi khi xóa danh mục.";
    }
    $_SESSION['success_message'] = 'Thao tác thành công!';

    // Chuyển hướng về trang danh sách danh mục sau khi xóa
    header("Location: index.php");
    exit();
} else {
    // Hiển thị thông báo nếu không có category_id
    echo "Không có danh mục để xóa";
    exit();
}
?>
