<?php
// Include file kết nối CSDL
include_once '../dbconnect.php';

// Kiểm tra xem đã nhận được news_id hay chưa
if (isset($_GET['news_id'])) {
    $news_id = $_GET['news_id'];

    // Truy vấn dữ liệu của tin tức dựa vào news_id
    $query = "SELECT news_name, news_image, content FROM news WHERE news_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $news_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $news_name, $news_image, $content);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // Nếu form được submit, cập nhật tin tức trong CSDL
    if (isset($_POST['update'])) {
        $new_news_name = $_POST['news_name'];
        $new_content = $_POST['content'];

        // Xử lý upload ảnh mới nếu có
        if ($_FILES['news_image']['name']) {
            $upload_dir = '../assets/img/imgnews/';
            $new_news_image = $upload_dir . basename($_FILES['news_image']['name']);
            move_uploaded_file($_FILES['news_image']['tmp_name'], $new_news_image);
        } else {
            $new_news_image = $news_image;
        }

        // Cập nhật dữ liệu tin tức
        $update_query = "UPDATE news SET news_name = ?, news_image = ?, content = ? WHERE news_id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, "sssi", $new_news_name, $new_news_image, $new_content, $news_id);
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);

        // Thêm thông báo thành công vào session
        $_SESSION['success_message'] = "Cập nhật tin tức thành công!";

        // Chuyển hướng về trang danh sách tin tức sau khi cập nhật thành công
        header("Location: index.php");
        exit();
    }
} else {
    // Nếu không nhận được news_id, chuyển hướng về trang danh sách tin tức
    header("Location: index.php");
    exit();
}

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <title>Chỉnh sửa tin tức</title>
    <link rel="stylesheet" href="../assets/css/modal.css">
    <style>
        .container {
            margin-top: 80px;
        }
    </style>
</head>

<body>

    <?php
    // Include header
    include_once '../header.php';
    ?>
    
    <div class="container">
    <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-left-long"></i></a>
        <h2 class="my-4 text-center">Chỉnh sửa tin tức</h2>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="news_name">Tiêu đề tin tức</label>
                <input type="text" class="form-control" id="news_name" name="news_name" value="<?php echo htmlspecialchars($news_name); ?>" required>
            </div>
            <div class="form-group">
                <label for="news_image">Ảnh tin tức</label>
                <input type="file" class="form-control-file" id="news_image" name="news_image">
                <img src="<?php echo htmlspecialchars($news_image); ?>" alt="Ảnh tin tức" width="150" class="mt-2">
            </div>
            <div class="form-group">
                <label for="content">Nội dung</label>
                <textarea class="form-control" id="content" name="content" rows="5" required><?php echo htmlspecialchars($content); ?></textarea>
            </div>
            <button type="submit" name="update" class="btn btn-primary">Cập nhật tin tức</button>
            <a href="index.php" class="btn btn-secondary">Hủy bỏ</a>
        </form>
    </div>

</body>

</html>

<?php
// Đóng kết nối CSDL
mysqli_close($conn);
?>
