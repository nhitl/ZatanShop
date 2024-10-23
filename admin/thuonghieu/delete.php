<?php
session_start(); // Bắt đầu session
include_once '../dbconnect.php';

// Kiểm tra nếu có brand_id được truyền từ URL
if (isset($_GET['brand_id'])) {
    $brandId = $_GET['brand_id'];

    // Truy vấn CSDL để lấy thông tin chi tiết thương hiệu
    $query = "SELECT brand_image FROM brands WHERE brand_id = $brandId";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // Xóa ảnh thương hiệu từ thư mục
        $imagePath = $row['brand_image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        // Xóa thông tin thương hiệu từ CSDL
        $deleteBrandQuery = "DELETE FROM brands WHERE brand_id = $brandId";
        if (mysqli_query($conn, $deleteBrandQuery)) {
            // Thiết lập thông báo thành công
            $_SESSION['success_message'] = "Thương hiệu đã được xóa thành công.";
        } else {
            $_SESSION['success_message'] = "Có lỗi xảy ra khi xóa thương hiệu.";
        }

    } else {
        $_SESSION['success_message'] = "Không tìm thấy thương hiệu.";
    }
} else {
    $_SESSION['success_message'] = "Không có thương hiệu để xóa.";
}

// Chuyển hướng người dùng về trang danh sách thương hiệu sau khi xóa
header("Location: index.php");
exit();
?>
