<?php
// Include file kết nối CSDL
include_once '../dbconnect.php';

// Truy vấn CSDL
$query = "SELECT user_id, email, phone_number, `password`, role FROM users";

// Thực hiện truy vấn
$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Cập nhật Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>Danh sách người dùng</title>
    <link rel="stylesheet" href="../assets/css/modal.css">
    <style>
        .container-fluid {
            padding-top: 80px;
        }
    </style>
</head>

<body>
    <?php include_once '../header.php';
    include_once '../notification.php';
    ?>

    <div class="container-fluid">
        <div class="row">
            <main role="main" class="col-md-9 ms-sm-auto col-lg-10 px-4">
                <h2 class="my-4">Danh sách người dùng</h2>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Email</th>
                                <th>Số điện thoại</th>
                                <th>Vai trò</th>
                                <th>Quyền admin</th>
                                <th>Hành động</th> <!-- Cột mới -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><?php echo $row['user_id']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td><?php echo $row['phone_number']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo ($row['role'] == 1 ? 'success' : 'secondary'); ?>">
                                            <?php echo ($row['role'] == 1 ? 'Admin' : 'Khách Hàng'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input toggle-role" type="checkbox"
                                                data-userid="<?php echo $row['user_id']; ?>"
                                                <?php echo ($row['role'] == 1 ? 'checked' : ''); ?>>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-href="delete_user.php?user_id=<?php echo $row['user_id']; ?>">
                                            <i class="fas fa-trash-alt"></i> Xóa
                                        </button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                </div>

            </main>
        </div>
    </div>

    <!-- Modal xác nhận xóa -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Xác nhận xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Bạn có chắc chắn muốn xóa tài khoản này không?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <a href="#" class="btn btn-danger" id="confirmDelete">Xóa</a>
                </div>
            </div>
        </div>
    </div>

    <?php include_once '../footer.php'; ?>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var deleteModal = document.getElementById('deleteModal');
            deleteModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var href = button.getAttribute('data-href');
                document.getElementById('confirmDelete').setAttribute('href', href);
            });
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $(".toggle-role").change(function() {
                let user_id = $(this).data("userid");
                let new_role = $(this).is(":checked") ? 1 : 0;

                $.ajax({
                    url: "update_role.php",
                    type: "POST",
                    data: {
                        user_id: user_id,
                        role: new_role
                    },
                    success: function(response) {
                        console.log(response);
                        location.reload(); // Làm mới trang để cập nhật giao diện
                    },
                    error: function() {
                        alert("Lỗi khi cập nhật quyền.");
                    }
                });
            });
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