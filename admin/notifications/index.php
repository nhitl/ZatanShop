<?php
include_once '../dbconnect.php';

// Xử lý thêm thông báo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addNotification'])) {
    $title = $_POST['title'];
    $message = $_POST['message'];
    $status = $_POST['status'];

    $sql = "INSERT INTO notifications (title, message, status) VALUES ('$title', '$message', '$status')";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success_message'] = "Thông báo đã được thêm thành công!";
    }
    header("Location: index.php");
    exit();
}

// Xử lý cập nhật thông báo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editNotification'])) {
    $id = $_POST['notification_id'];
    $title = $_POST['title'];
    $message = $_POST['message'];
    $status = $_POST['status'];

    $sql = "UPDATE notifications SET title='$title', message='$message', status='$status' WHERE notification_id=$id";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success_message'] = "Thông báo đã được cập nhật thành công!";
    }
    header("Location: index.php");
    exit();
}

// Xử lý xóa thông báo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']); // Chuyển ID thành số nguyên để tránh lỗi

    $stmt = $conn->prepare("DELETE FROM notifications WHERE notification_id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Thông báo đã được xóa thành công!";
    } else {
        $_SESSION['error_message'] = "Lỗi khi xóa thông báo: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý thông báo</title>
    <link rel="stylesheet" href="../assets/css/modal.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    include_once '../notification.php';
    ?>
    <div class="container">
        <a href="../index.php" class="btn btn-secondary"><i class="fa-solid fa-left-long"></i></a>
        <h2 class="mt-2 text-center">Quản lý thông báo</h2>

        <!-- Nút thêm thông báo -->
        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal">Thêm thông báo mới</button>

        <!-- Hiển thị thông báo dưới dạng thẻ -->
        <div class="row">
            <?php
            $sql = "SELECT * FROM notifications";
            $result = $conn->query($sql);

            while ($row = $result->fetch_assoc()) {
                echo "<div class='col-12 col-md-6 col-lg-4 mb-4'>
                        <div class='card h-100'>
                            <div class='card-body'>
                                <h5 class='card-title'>{$row['title']}</h5>
                                <p class='card-text'>{$row['message']}</p>
                                <p class='card-text'><small class='text-muted'>Ngày tạo: {$row['created_at']}</small></p>
                                <p class='card-text'><span class='badge " . ($row['status'] == 'active' ? 'bg-success' : 'bg-secondary') . "'>{$row['status']}</span></p>
                                <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editModal' data-id='{$row['notification_id']}' data-title='{$row['title']}' data-message='{$row['message']}' data-status='{$row['status']}'>Sửa</button>
                                <a href='#' class='btn btn-danger btn-sm' data-bs-toggle='modal' data-bs-target='#deleteModal' data-id='{$row['notification_id']}'>Xóa</a>
                            </div>
                        </div>
                    </div>";
            }
            ?>
        </div>
    </div>


    <!-- Modal Thêm Thông báo -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Thêm Thông báo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Tiêu đề</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Nội dung</label>
                            <textarea class="form-control" name="message" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select class="form-select" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="addNotification" class="btn btn-primary">Thêm</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Sửa Thông báo -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Sửa Thông báo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="notification_id" id="edit-id">
                        <div class="mb-3">
                            <label for="title" class="form-label">Tiêu đề</label>
                            <input type="text" class="form-control" name="title" id="edit-title" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Nội dung</label>
                            <textarea class="form-control" name="message" id="edit-message" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select class="form-select" name="status" id="edit-status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="editNotification" class="btn btn-primary">Lưu</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal Xác nhận Xóa -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Xác nhận Xóa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Bạn có chắc chắn muốn xóa thông báo này không?
                        <input type="hidden" name="delete_id" id="delete-id">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Xóa</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Chuyển dữ liệu vào modal sửa
        var editModal = document.getElementById('editModal');
        editModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var title = button.getAttribute('data-title');
            var message = button.getAttribute('data-message');
            var status = button.getAttribute('data-status');

            document.getElementById('edit-id').value = id;
            document.getElementById('edit-title').value = title;
            document.getElementById('edit-message').value = message;
            document.getElementById('edit-status').value = status;
        });
        var deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            document.getElementById('delete-id').value = id;
        });
    </script>
</body>

</html>