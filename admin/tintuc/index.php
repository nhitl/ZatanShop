<?php
// Include file kết nối CSDL
include_once '../dbconnect.php';
include_once '../notification.php';

// Truy vấn CSDL
$query = "SELECT news_id, news_name, news_image, content FROM news";

// Thực hiện truy vấn
$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Tin tức</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .container-fluid {
            margin-top: 80px;
        }
    </style>
</head>

<body>
    <?php include_once '../header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <main role="main" class="col-md-9 ms-sm-auto col-lg-10 px-4">
                <h2 class="my-4 text-center">Danh sách tin tức</h2>
                <div class="mb-3">
                    <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Tạo tin tức mới</a>
                </div>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tiêu đề tin tức</th>
                            <th>Ảnh tin tức</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                            <tr>
                                <td><?= $row['news_id']; ?></td>
                                <td><?= htmlspecialchars($row['news_name']); ?></td>
                                <td><img src="<?= htmlspecialchars($row['news_image']); ?>" alt="Ảnh tin tức" width="90"></td>
                                <td>
                                    <a href="view.php?news_id=<?= $row['news_id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> Xem</a>
                                    <a href="edit.php?news_id=<?= $row['news_id']; ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> Sửa</a>
                                    <a href="#" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-href="delete.php?news_id=<?= $row['news_id']; ?>">
                                        <i class="fas fa-trash-alt"></i> Xóa
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </main>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Xác nhận xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn xóa tin tức này không?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <a href="#" id="confirmDelete" class="btn btn-danger">Xóa</a>
                </div>
            </div>
        </div>
    </div>

    <?php include_once '../footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('deleteModal').addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var href = button.getAttribute('data-href');
            document.getElementById('confirmDelete').setAttribute('href', href);
        });
    </script>
</body>

</html>

<?php
// Giải phóng bộ nhớ
mysqli_free_result($result);

// Đóng kết nối CSDL
mysqli_close($conn);
?>