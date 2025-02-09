<?php
session_start(); // Khởi động session
include_once '../dbconnect.php';

// Kiểm tra xem có tham số category_id được truyền không
if (isset($_GET['category_id'])) {
    $categoryId = $_GET['category_id'];

    // Kiểm tra xem danh mục có chứa danh mục con hay không
    $checkSubcategoriesQuery = "SELECT * FROM subcategories WHERE category_id = $categoryId";
    $result = mysqli_query($conn, $checkSubcategoriesQuery);

    if (mysqli_num_rows($result) > 0) {
        // Nếu có danh mục con, hiển thị thông báo
        $_SESSION['error_message'] = "Không thể xóa danh mục này vì nó có liên kết với danh mục con.";
    } else {
        // Nếu không có danh mục con, tiến hành xóa danh mục
        $deleteCategoryQuery = "DELETE FROM categories WHERE category_id = $categoryId";

        if (mysqli_query($conn, $deleteCategoryQuery)) {
            // Thiết lập thông báo thành công
            $_SESSION['success_message'] = "Danh mục đã được xóa thành công!";
        } else {
            // Thiết lập thông báo lỗi nếu xóa không thành công
            $_SESSION['error_message'] = "Đã xảy ra lỗi khi xóa danh mục.";
        }
    }

    // Chuyển hướng về trang danh sách danh mục sau khi xóa
    header("Location: index.php");
    exit();
} else {
    // Hiển thị thông báo nếu không có category_id
    echo "Không có danh mục để xóa";
    exit();
}
?>
