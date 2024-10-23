<?php
// Include file kết nối CSDL
include_once '../dbconnect.php';
// Kiểm tra nếu có brand_id được truyền từ URL
if (isset($_GET['brand_id'])) {
    $brandId = $_GET['brand_id'];

    // Truy vấn CSDL để lấy thông tin chi tiết thương hiệu
    $query = "SELECT * FROM brands WHERE brand_id = $brandId";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
    } else {
        // Hiển thị thông báo nếu không tìm thấy thương hiệu
        echo "Không tìm thấy thương hiệu";
        exit();
    }
} else {
    // Hiển thị thông báo nếu không có brand_id
    echo "Không có thương hiệu để hiển thị";
    exit();
}

// Xử lý khi form được submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $brandName = $_POST['brand_name'];

    // Xử lý ảnh thương hiệu
    $targetDir = "../assets/img/imgbrands/";

    // Lấy ngày tháng năm hiện tại
    $currentDate = date("YmdHis");

    // Upload ảnh thương hiệu
    $targetFile = $targetDir . $currentDate . "_" . basename($_FILES["brand_image"]["name"]);
    if (move_uploaded_file($_FILES["brand_image"]["tmp_name"], $targetFile)) {
        // Cập nhật thông tin thương hiệu trong bảng brands
        $updateBrandQuery = "UPDATE brands SET 
                               brand_name = '$brandName', 
                               brand_image = '$targetFile' 
                               WHERE brand_id = $brandId";
    } else {
        // Nếu không upload được ảnh, chỉ cập nhật tên thương hiệu
        $updateBrandQuery = "UPDATE brands SET 
                               brand_name = '$brandName' 
                               WHERE brand_id = $brandId";
    }

    if (mysqli_query($conn, $updateBrandQuery)) {
        $_SESSION['success_message'] = 'Cập nhật thương hiệu thành công!';
    } else {
        $_SESSION['success_message'] = 'Có lỗi xảy ra khi cập nhật thương hiệu.';
    }

    // Chuyển hướng về trang danh sách thương hiệu sau khi cập nhật
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
    <title>Chỉnh sửa thương hiệu</title>
    <link rel="stylesheet" href="../assets/css/modal.css">
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
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
                <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-left-long"></i></a>
                <h2 class="my-4">Chỉnh sửa thương hiệu</h2>

                <!-- Form chỉnh sửa thương hiệu -->
                <form action="edit.php?brand_id=<?php echo $brandId; ?>" method="post" enctype="multipart/form-data">
                    <!-- Các trường thông tin thương hiệu -->
                    <div class="form-group">
                        <label for="brand_name">Tên thương hiệu:</label>
                        <input type="text" class="form-control" id="brand_name" name="brand_name" value="<?php echo htmlspecialchars($row['brand_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="brand_image">Ảnh thương hiệu:</label>
                        <input type="file" class="form-control-file" id="brand_image" name="brand_image" accept="image/*">
                        <img src="<?php echo htmlspecialchars($row['brand_image']); ?>" alt="Ảnh thương hiệu" width="100">
                    </div>
                    <!-- Nút cập nhật -->
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </form>

            </main>
        </div>
    </div>

</body>

</html>

<?php
// Đóng kết nối CSDL
mysqli_close($conn);
?>
