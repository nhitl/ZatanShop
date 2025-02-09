<?php
// Include file kết nối CSDL
include_once '../dbconnect.php';

// Khởi động session
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $brandName = $_POST['brand_name'];

    // Xử lý ảnh thương hiệu
    $targetDir = "../assets/img/imgbrands/";
    $currentDate = date("YmdHis");
    $targetFile = $targetDir . $currentDate . "_" . basename($_FILES["brand_image"]["name"]);
    move_uploaded_file($_FILES["brand_image"]["tmp_name"], $targetFile);

    // Thêm mới thương hiệu vào CSDL
    $insertBrandQuery = "INSERT INTO brands (brand_name, brand_image) VALUES ('$brandName', '$targetFile')";

    if (mysqli_query($conn, $insertBrandQuery)) {
        $_SESSION['success_message'] = "Thương hiệu đã được thêm mới thành công!";
    } else {
        $_SESSION['success_message'] = "Có lỗi xảy ra khi thêm thương hiệu.";
    }

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thêm mới thương hiệu</title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/modal.css">

    <style>
        .cr-brand .container {
            margin-top: 80px;
            min-height: 500px;
            width: 80%;
        }
    </style>
</head>

<body>

    <?php include_once '../header.php'; ?>
    <section class="cr-brand">
        <div class="container">
            <a href="index.php" class="btn btn-secondary mb-3"><i class="fa-solid fa-arrow-left"></i> Quay lại</a>
            <h2 class="text-center mb-4">Thêm mới thương hiệu</h2>

            <!-- Form thêm thương hiệu -->
            <form action="create.php" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="brand_name" class="form-label">Tên thương hiệu:</label>
                    <input type="text" class="form-control" id="brand_name" name="brand_name" required>
                </div>
                <div class="mb-3">
                    <label for="brand_image" class="form-label">Ảnh thương hiệu:</label>
                    <input type="file" class="form-control" id="brand_image" name="brand_image" accept="image/*" required>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Thêm mới</button>
            </form>
        </div>
    </section>


    <?php include_once '../footer.php'; ?>

    <!-- Bootstrap 5 Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php mysqli_close($conn); ?>