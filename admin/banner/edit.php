<?php
// Bắt đầu phiên làm việc
session_start();

// Kết nối CSDL
include_once '../dbconnect.php';

// Kiểm tra xem ID banner có tồn tại không
if (isset($_GET['id'])) {
    $banner_id = $_GET['id'];

    // Lấy thông tin banner từ CSDL
    $sql = "SELECT * FROM banners WHERE banner_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $banner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $banner = $result->fetch_assoc();

    if (!$banner) {
        $_SESSION['error_message'] = "Không tìm thấy banner với ID này!";
        header("Location: index.php");
        exit();
    }

    // Cập nhật banner khi form được gửi
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $banner_name = $_POST['banner_name'];
        $banner_content = $_POST['content'];
        $banner_link = $_POST['link'];

        // Xử lý upload ảnh
        $new_image = $banner['banner_image']; // Giữ ảnh cũ mặc định
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = '../assets/img/imgbanners/'; // Thư mục lưu ảnh
            $new_image = $upload_dir . basename($_FILES['image']['name']);

            // Di chuyển file tải lên vào thư mục
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $new_image)) {
                $_SESSION['error_message'] = "Có lỗi xảy ra khi tải lên ảnh.";
            }
        }

        // Cập nhật dữ liệu banner trong CSDL
        $sql = "UPDATE banners SET banner_name = ?, content = ?, link = ?, banner_image = ? WHERE banner_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $banner_name, $banner_content, $banner_link, $new_image, $banner_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Cập nhật banner thành công!";
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Cập nhật thất bại!";
        }
    }
} else {
    $_SESSION['error_message'] = "Không có ID banner được cung cấp!";
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa Banner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/modal.css">
</head>

<body>
    <?php
    include_once '../header.php';
    ?>
    <section class="edit-bn">
        <div class="container mt-5">
        <a href="index.php" class="btn btn-secondary mt-5"><i class="fa-solid fa-left-long"></i></a>
            <h1 class="text-center">Chỉnh sửa Banner</h1>

            <!-- Hiển thị thông báo thành công -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success_message'] ?>
                    <?php unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <!-- Hiển thị thông báo lỗi -->
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['error_message'] ?>
                    <?php unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="banner_name" class="form-label">Tên Banner</label>
                    <input type="text" class="form-control" id="banner_name" name="banner_name" value="<?= $banner['banner_name'] ?>" required>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">Nội dung</label>
                    <textarea class="form-control" id="content" name="content" rows="4" required><?= $banner['content'] ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="link" class="form-label">Liên kết</label>
                    <input type="text" class="form-control" id="link" name="link" value="<?= $banner['link'] ?>">
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Hình ảnh mới (nếu có)</label>
                    <input type="file" class="form-control" id="image" name="image">
                    <small class="form-text text-muted">Để trống nếu không muốn thay đổi ảnh.</small>
                </div>

                <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                <a href="index.php" class="btn btn-secondary">Quay lại</a>
            </form>
        </div>
    </section>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$conn->close();
?>