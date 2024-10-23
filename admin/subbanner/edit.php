<?php
// Bắt đầu phiên làm việc
session_start();

// Kết nối CSDL
include_once '../dbconnect.php';

// Kiểm tra xem ID subbanner có tồn tại không
if (isset($_GET['id'])) {
    $subbanner_id = $_GET['id'];

    // Lấy thông tin subbanner từ CSDL
    $sql = "SELECT * FROM subbanners WHERE subbanner_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $subbanner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $subbanner = $result->fetch_assoc();

    if (!$subbanner) {
        $_SESSION['error_message'] = "Không tìm thấy subbanner với ID này!";
        header("Location: index.php");
        exit();
    }

    // Cập nhật subbanner khi form được gửi
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $subbanner_name = $_POST['subbanner_name'];
        $subbanner_content = $_POST['content'];
        $subbanner_link = $_POST['link'];

        // Xử lý upload ảnh
        $new_image = $subbanner['subbanner_image']; // Giữ ảnh cũ mặc định
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = '../assets/img/imgsubbanners/'; // Thư mục lưu ảnh
            $new_image = $upload_dir . basename($_FILES['image']['name']);

            // Di chuyển file tải lên vào thư mục
            if (move_uploaded_file($_FILES['image']['tmp_name'], $new_image)) {
                // Nếu tải lên thành công, cập nhật đường dẫn ảnh mới
            } else {
                $_SESSION['error_message'] = "Có lỗi xảy ra khi tải lên ảnh.";
            }
        }

        // Cập nhật dữ liệu subbanner trong CSDL
        $sql = "UPDATE subbanners SET subbanner_name = ?, content = ?, link = ?, subbanner_image = ? WHERE subbanner_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $subbanner_name, $subbanner_content, $subbanner_link, $new_image, $subbanner_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Cập nhật subbanner thành công!";
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Cập nhật thất bại!";
        }
    }
} else {
    $_SESSION['error_message'] = "Không có ID subbanner được cung cấp!";
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa Subbanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/modal.css">
</head>

<body>
    <?php
    include_once '../header.php';
    ?>
    <section class="edit-subbn">
        <div class="container mt-5">
        <a href="index.php" class="btn btn-secondary mt-4"><i class="fa-solid fa-left-long"></i></a>
            <h1 class="text-center">Chỉnh sửa Banner nhỏ</h1>

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
                    <label for="subbanner_name" class="form-label">Tên Subbanner</label>
                    <input type="text" class="form-control" id="subbanner_name" name="subbanner_name" value="<?= $subbanner['subbanner_name'] ?>" required>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">Nội dung</label>
                    <textarea class="form-control" id="content" name="content" rows="4" required><?= $subbanner['content'] ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="link" class="form-label">Liên kết</label>
                    <input type="text" class="form-control" id="link" name="link" value="<?= $subbanner['link'] ?>">
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