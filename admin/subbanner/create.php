<?php
// Include file kết nối CSDL
include_once '../dbconnect.php';

// Xử lý khi form được submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $subbannerName = $_POST['subbanner_name'];
    $content = $_POST['content'];
    $link = $_POST['link'];

    // Xử lý ảnh subbanner
    $targetDir = "../assets/img/imgsubbanners/";

    // Lấy ngày tháng năm hiện tại
    $currentDate = date("YmdHis");

    // Upload ảnh subbanner
    $targetFile = $targetDir . $currentDate . "_" . basename($_FILES["subbanner_image"]["name"]);
    if (move_uploaded_file($_FILES["subbanner_image"]["tmp_name"], $targetFile)) {
        // Thêm mới thông tin subbanner vào CSDL
        $insertSubbannerQuery = "INSERT INTO subbanners (subbanner_name, subbanner_image, content, link) VALUES ('$subbannerName', '$targetFile', '$content', '$link')";

        if (mysqli_query($conn, $insertSubbannerQuery)) {
            // Đặt thông điệp thành công vào session
            $_SESSION['success_message'] = 'Subbanner đã được thêm thành công!';
        } else {
            $_SESSION['success_message'] = 'Có lỗi khi thêm subbanner!';
        }
    } else {
        $_SESSION['success_message'] = 'Có lỗi khi tải ảnh lên!';
    }

    // Chuyển hướng về trang danh sách subbanner sau khi thêm mới
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/modal.css">
    <title>Thêm mới Subbanner</title>
    <style>
        .container-fluid {
            margin-top: 80px;
        }
    </style>
</head>

<body>

    <?php
    // Include header
    include_once '../header.php';
    ?>

    <div class="container-fluid">
        <div class="row">
            <main role="main" class="col-md-9 ms-sm-auto col-lg-10 px-4">
                <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-left-long"></i></a>
                <h2 class="my-4">Thêm mới Subbanner</h2>

                <?php
                // Hiển thị thông điệp thành công nếu có
                if (isset($_SESSION['success_message'])) {
                    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
                    // Xóa thông điệp sau khi hiển thị
                    unset($_SESSION['success_message']);
                }
                ?>

                <!-- Form thêm mới subbanner -->
                <form action="create.php" method="post" enctype="multipart/form-data">
                    <!-- Các trường thông tin subbanner -->
                    <div class="mb-3">
                        <label for="subbanner_name" class="form-label">Tên subbanner:</label>
                        <input type="text" class="form-control" id="subbanner_name" name="subbanner_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="subbanner_image" class="form-label">Ảnh subbanner:</label>
                        <input type="file" class="form-control" id="subbanner_image" name="subbanner_image" accept="image/*" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Nội dung:</label>
                        <textarea class="form-control" id="content" name="content"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="link" class="form-label">Link:</label>
                        <input type="text" class="form-control" id="link" name="link">
                    </div>
                    <!-- Nút thêm mới -->
                    <button type="submit" class="btn btn-primary">Thêm mới</button>
                </form>

            </main>
        </div>
    </div>
    
    <?php
    include_once '../footer.php';
    ?>
</body>

</html>

