<?php
include_once '../dbconnect.php';
include_once '../notification.php'; // Bao gồm file thông báo thành công

$query = "SELECT subcategory_id, subcategory_name, category_id FROM subcategories";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Danh mục con</title>
    <link rel="stylesheet" href="../../assets/css/modal.css">
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
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
                <h2 class="my-4 text-center">Danh sách danh mục con</h2>
                <div class="mb-3">
                    <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Tạo mới danh mục con</a>
                </div>
                <!-- Table to display subcategory list -->
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên danh mục con</th>
                            <th>Danh mục cha</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = mysqli_fetch_assoc($result)) {
                            $category_query = "SELECT category_name FROM categories WHERE category_id = {$row['category_id']}";
                            $category_result = mysqli_query($conn, $category_query);
                            $category = mysqli_fetch_assoc($category_result);

                            echo "<tr>";
                            echo "<td>{$row['subcategory_id']}</td>";
                            echo "<td>{$row['subcategory_name']}</td>";
                            echo "<td>{$category['category_name']}</td>";
                            echo "<td>
                                    <a href='edit.php?subcategory_id={$row['subcategory_id']}' class='btn btn-primary btn-sm'><i class='fas fa-edit'></i> Chỉnh sửa</a>
                                    <button class='btn btn-danger btn-sm' data-toggle='modal' data-target='#confirmDeleteModal' data-id='{$row['subcategory_id']}'><i class='fas fa-trash-alt'></i> Xóa</button>
                                    <a href='#' class='btn btn-info btn-sm' data-toggle='modal' data-target='#subcategoryDetailModal{$row['subcategory_id']}'><i class='fas fa-eye'></i> Xem chi tiết</a>
                                </td>";
                            echo "</tr>";

                            echo "<div class='modal fade' id='subcategoryDetailModal{$row['subcategory_id']}' tabindex='-1' role='dialog' aria-labelledby='subcategoryDetailModalLabel' aria-hidden='true'>
                                    <div class='modal-dialog' role='document'>
                                        <div class='modal-content'>
                                            <div class='modal-header'>
                                                <h5 class='modal-title' id='subcategoryDetailModalLabel'>Xem chi tiết Danh mục con</h5>
                                                <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                                                    <span aria-hidden='true'>&times;</span>
                                                </button>
                                            </div>
                                            <div class='modal-body'>
                                                <p><strong>ID:</strong> {$row['subcategory_id']}</p>
                                                <p><strong>Tên danh mục con:</strong> {$row['subcategory_name']}</p>
                                                <p><strong>Danh mục cha:</strong> {$category['category_name']}</p>
                                            </div>
                                            <div class='modal-footer'>
                                                <button type='button' class='btn btn-secondary' data-dismiss='modal'>Đóng</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>";
                        }
                        ?>
                    </tbody>
                </table>
            </main>
        </div>
    </div>
    <!-- Modal xác nhận xóa -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Xác nhận xóa</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn xóa danh mục con này không?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <a href="#" class="btn btn-danger" id="confirmDeleteBtn">Xóa</a>
                </div>
            </div>
        </div>
    </div>
    <script>
        $('#confirmDeleteModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); 
            var id = button.data('id'); 
            var modal = $(this);
            modal.find('#confirmDeleteBtn').attr('href', 'delete.php?subcategory_id=' + id);
        });
    </script>
                    
</body>
</html>
