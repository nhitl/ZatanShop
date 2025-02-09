<?php
// Include file kết nối CSDL
include_once '../dbconnect.php';
include_once '../notification.php';
// Truy vấn CSDL
$query = "SELECT brand_id, brand_name, brand_image FROM brands";

// Thực hiện truy vấn
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>Thương hiệu</title>
    <link rel="stylesheet" href="../../assets/css/modal.css">
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
                <h2 class="my-4 text-center">Danh sách thương hiệu</h2>
                <div class="mb-3">
                    <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Thêm mới thương hiệu</a>
                </div>

                <!-- Table to display brand list -->
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên thương hiệu</th>
                            <th>Ảnh thương hiệu</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>{$row['brand_id']}</td>";
                            echo "<td>{$row['brand_name']}</td>";
                            echo "<td><img src='{$row['brand_image']}' alt='Ảnh thương hiệu' width='50'></td>";
                            echo "<td>
                                <a href='edit.php?brand_id={$row['brand_id']}' class='btn btn-primary btn-sm'><i class='fas fa-edit'></i> Chỉnh sửa</a>
                                <a href='#' class='btn btn-danger btn-sm' data-bs-toggle='modal' data-bs-target='#confirmDeleteModal' data-href='delete.php?brand_id={$row['brand_id']}'><i class='fas fa-trash-alt'></i> Xóa</a>
                                </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </main>
        </div>
    </div>

    <!-- Modal Xác nhận Xóa -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteLabel">Xác nhận xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn xóa thương hiệu này không?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <a id="confirmDeleteButton" href="#" class="btn btn-danger">Xóa</a>
                </div>
            </div>
        </div>
    </div>

    <?php
    include_once '../footer.php';
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('confirmDeleteModal').addEventListener('show.bs.modal', function(event) {
            var deleteButton = event.relatedTarget;
            var href = deleteButton.getAttribute('data-href');
            document.getElementById('confirmDeleteButton').setAttribute('href', href);
        });
    </script>
</body>

</html>

<?php
mysqli_free_result($result);
mysqli_close($conn);
?>
