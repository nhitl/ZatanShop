<?php
session_start(); // Đảm bảo session được khởi tạo
include_once '../dbconnect.php';

if (isset($_GET['subcategory_id'])) {
    $subcategory_id = intval($_GET['subcategory_id']);
    $query = "DELETE FROM subcategories WHERE subcategory_id = $subcategory_id";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success_message'] = "Danh mục con đã được xóa thành công.";
    } else {
        $_SESSION['success_message'] = "Có lỗi xảy ra khi xóa danh mục con.";
    }
    
    mysqli_close($conn);
    header("Location: index.php"); // Chuyển hướng về trang danh sách
    exit();
}
?>
