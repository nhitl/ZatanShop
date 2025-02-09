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
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>Chỉnh sửa thương hiệu</title>
    <link rel="stylesheet" href="../assets/css/modal.css">
    <style>
        .ed-brand .container{
            margin-top: 40px;
        }
    </style>
</head>

<body>
    <?php include_once '../header.php'; ?>
    <section class="ed-brand">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <a href="index.php" class="btn btn-secondary mb-3"><i class="fa fa-arrow-left"></i> Quay lại</a>
                    <h2 class="text-center mb-4">Chỉnh sửa thương hiệu</h2>
                    <form action="edit.php?brand_id=<?php echo $brandId; ?>" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="brand_name" class="form-label">Tên thương hiệu:</label>
                            <input type="text" class="form-control" id="brand_name" name="brand_name" value="<?php echo htmlspecialchars($row['brand_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="brand_image" class="form-label">Ảnh thương hiệu:</label>
                            <input type="file" class="form-control" id="brand_image" name="brand_image" accept="image/*">
                            <div class="mt-3">
                                <img src="<?php echo htmlspecialchars($row['brand_image']); ?>" alt="Ảnh thương hiệu" class="img-thumbnail" width="150">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">Cập nhật</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <?php include_once '../footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php mysqli_close($conn); ?>