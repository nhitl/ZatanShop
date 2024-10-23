<?php
// Include file kết nối CSDL
include_once '../dbconnect.php';

// Kiểm tra xem có tham số news_id được truyền không
if (isset($_GET['news_id'])) {
    $newsId = $_GET['news_id'];

    // Truy vấn CSDL để lấy thông tin chi tiết tin tức
    $query = "SELECT news_image FROM news WHERE news_id = $newsId";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // Lấy đường dẫn ảnh tin tức để xóa
        $imagePath = $row['news_image'];

        // Xóa ảnh từ thư mục
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        // Xóa tin tức từ CSDL
        $deleteNewsQuery = "DELETE FROM news WHERE news_id = $newsId";
        if (mysqli_query($conn, $deleteNewsQuery)) {
            // Thêm thông báo thành công vào session
            $_SESSION['success_message'] = "Tin tức đã được xóa thành công!";
        }

        // Chuyển hướng về trang danh sách tin tức sau khi xóa
        header("Location: index.php");
        exit();
    } else {
        // Hiển thị thông báo nếu không tìm thấy tin tức
        echo "Không tìm thấy tin tức";
        exit();
    }
} else {
    // Hiển thị thông báo nếu không có news_id
    echo "Không có tin tức để xóa";
    exit();
}
?>
