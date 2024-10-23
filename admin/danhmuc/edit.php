<?php
// Include file kết nối CSDL
include_once '../dbconnect.php';

// Kiểm tra xem có tham số category_id được truyền không
if (isset($_GET['category_id'])) {
    $categoryId = $_GET['category_id'];

    // Truy vấn CSDL để lấy thông tin chi tiết danh mục
    $query = "SELECT * FROM categories WHERE category_id = $categoryId";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
    } else {
        // Hiển thị thông báo nếu không tìm thấy danh mục
        echo "Không tìm thấy danh mục";
        exit();
    }
} else {
    // Hiển thị thông báo nếu không có category_id
    echo "Không có danh mục để hiển thị";
    exit();
}

// Xử lý khi form được submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $categoryName = $_POST['category_name'];
    $categoryContent = $_POST['category_content'];
    
    // Xử lý upload ảnh
    $uploadFileDir = '../assets/img/imgcategory/';
    $imageFileName = $_FILES['category_image']['name'];

    // Đảm bảo rằng tên tệp không bị rỗng (tức là người dùng đã upload ảnh mới)
    if (!empty($imageFileName)) {
        $imageFilePath = $uploadFileDir . $imageFileName;

        if (move_uploaded_file($_FILES['category_image']['tmp_name'], $imageFilePath)) {
            echo "Ảnh đã được tải lên thành công.";

            // Lưu đường dẫn đầy đủ vào CSDL
            $categoryImagePath = 'assets/img/imgcategory/' . $imageFileName;
        } else {
            echo "Có lỗi xảy ra khi tải ảnh.";
            $categoryImagePath = $row['category_image']; // Giữ nguyên đường dẫn cũ nếu upload thất bại
        }
    } else {
        $categoryImagePath = $row['category_image']; // Giữ nguyên đường dẫn cũ nếu không có ảnh mới
    }

    // Cập nhật thông tin danh mục trong bảng categories
    $updateCategoryQuery = "UPDATE categories SET 
                            category_name = '$categoryName', 
                            category_content = '$categoryContent', 
                            category_image = '$categoryImagePath' 
                            WHERE category_id = $categoryId";

    mysqli_query($conn, $updateCategoryQuery);
    $_SESSION['success_message'] = 'Thao tác thành công!';
    // Chuyển hướng về trang danh sách danh mục sau khi cập nhật
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
    <link rel="stylesheet" href="../assets/css/modal.css">
    <style>
        .container-fluid{
            padding-top: 80px;
    }
    </style>
    <title>Chỉnh sửa danh mục</title>
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
                <h2 class="my-4">Chỉnh sửa danh mục</h2>

                <!-- Form chỉnh sửa danh mục -->
                <form action="edit.php?category_id=<?php echo $categoryId; ?>" method="post" enctype="multipart/form-data">
                    <!-- Các trường thông tin danh mục -->
                    <div class="form-group">
                        <label for="category_name">Tên danh mục:</label>
                        <input type="text" class="form-control" id="category_name" name="category_name" value="<?php echo $row['category_name']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="category_content">Nội dung danh mục:</label>
                        <textarea class="form-control" id="category_content" name="category_content"><?php echo $row['category_content']; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="category_image">Hình ảnh danh mục:</label>
                        <input type="file" class="form-control-file" id="category_image" name="category_image">
                        <?php if (!empty($row['category_image'])): ?>
                            <img src="../<?php echo $row['category_image']; ?>" alt="Hình ảnh danh mục" class="img-thumbnail mt-2" width="150">
                        <?php endif; ?>
                    </div>
                    <!-- Nút cập nhật -->
                    <button type="submit" class="btn btn-secondary">Cập nhật</button>
                </form>

            </main>
        </div>
    </div>

    

</body>

</html>

<?php
// Giải phóng bộ nhớ
mysqli_free_result($result);

// Đóng kết nối CSDL
?>
