<?php
// Bật báo cáo lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once '../dbconnect.php';

// Xử lý khi form được submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $category_content = mysqli_real_escape_string($conn, $_POST['category_content']);
    
    // Kiểm tra nếu có ảnh được chọn
    if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] === UPLOAD_ERR_OK) {
        $image_name = $_FILES['category_image']['name'];
        $image_tmp_name = $_FILES['category_image']['tmp_name'];
        $image_folder = '../assets/img/imgcategory/';
        
        // Kiểm tra và tạo thư mục nếu chưa tồn tại
        if (!is_dir($image_folder)) {
            if (!mkdir($image_folder, 0777, true)) {
                die("Không thể tạo thư mục lưu trữ ảnh.");
            }
        }
        
        $image_path = $image_folder . basename($image_name);

        // Di chuyển ảnh vào thư mục upload
        if (move_uploaded_file($image_tmp_name, $image_path)) {
            // Lưu đường dẫn ảnh trong cơ sở dữ liệu
            $db_image_path = 'assets/img/imgcategory/' . basename($image_name);
        } else {
            die("Lỗi khi tải ảnh lên.");
        }
    } else {
        die("Vui lòng chọn một ảnh hợp lệ.");
    }
    
    // Câu lệnh SQL để thêm mới danh mục bao gồm cả ảnh
    $sql = "INSERT INTO categories (category_name, category_content, category_image) VALUES ('$category_name', '$category_content', '$db_image_path')";
    
    // Thực hiện câu lệnh SQL và kiểm tra kết quả
    if (mysqli_query($conn, $sql)) {
        // Lưu thông báo vào session
        $_SESSION['success_message'] = 'Danh mục đã được thêm thành công!';
        // Chuyển hướng về trang danh sách danh mục con với tham số thành công
        header("Location: index.php?success=true");
        exit();
    }

 
    
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/modal.css">
    <title>Thêm mới Danh mục</title>
    <style>
        .container-fluid{
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
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-left-long"></i></a>
                <h2 class="my-4">Thêm mới Danh mục</h2>

                <!-- Form thêm mới danh mục -->
                <form action="create.php" method="post" enctype="multipart/form-data">
                    <!-- Các trường thông tin danh mục -->
                    <div class="form-group">
                        <label for="category_name">Tên danh mục:</label>
                        <input type="text" class="form-control" id="category_name" name="category_name" required>
                    </div>
                    <div class="form-group">
                        <label for="category_content">Nội dung danh mục:</label>
                        <textarea class="form-control" id="category_content" name="category_content"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="category_image">Ảnh danh mục:</label>
                        <input type="file" class="form-control-file" id="category_image" name="category_image" required>
                    </div>
                    <!-- Nút thêm mới -->
                    <button type="submit" class="btn btn-primary">Thêm mới</button>
                </form>

            </main>
        </div>
    </div>

</body>

</html>
