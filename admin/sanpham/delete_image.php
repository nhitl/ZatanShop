<?php
include '../dbconnect.php'; // Kết nối đến cơ sở dữ liệu

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imageId = $_POST['image_id'];

    // Lấy đường dẫn ảnh trước khi xóa
    $query = "SELECT image_url FROM product_images WHERE image_id = $imageId";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $imagePath = $row['image_url'];

    // Xóa ảnh trong cơ sở dữ liệu
    $deleteQuery = "DELETE FROM product_images WHERE image_id = $imageId";
    if (mysqli_query($conn, $deleteQuery)) {
        // Xóa file ảnh khỏi thư mục nếu cần
        if (file_exists($imagePath)) {
            unlink($imagePath); // Xóa file ảnh khỏi server
        }
        echo "success";
    } else {
        echo "error";
    }
}
?>
