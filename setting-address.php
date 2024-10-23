<?php
session_start();
include_once 'dbconnect.php';

if (!isset($_SESSION['user_id'])) {
    echo "Bạn cần đăng nhập để xem thông tin.";
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Xử lý Thêm Địa Chỉ
    if (isset($_POST['add_address'])) {
        $address = $_POST['address'];
        $recipient_name = $_POST['recipient_name'];
        $recipient_phone = $_POST['recipient_phone'];

        $sql = "INSERT INTO shipping_addresses (user_id, address, recipient_name, recipient_phone) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $user_id, $address, $recipient_name, $recipient_phone);
        if ($stmt->execute()) {
            $new_address_id = $stmt->insert_id;

            // Cập nhật địa chỉ mặc định
            $sql_update_default = "UPDATE shipping_addresses SET is_default = 0 WHERE user_id = ?";
            $stmt_update_default = $conn->prepare($sql_update_default);
            $stmt_update_default->bind_param("i", $user_id);
            $stmt_update_default->execute();

            $sql_set_default = "UPDATE shipping_addresses SET is_default = 1 WHERE address_id = ?";
            $stmt_set_default = $conn->prepare($sql_set_default);
            $stmt_set_default->bind_param("i", $new_address_id);
            $stmt_set_default->execute();

            $_SESSION['success_message'] = 'Địa chỉ đã được thêm thành công và đặt làm địa chỉ mặc định.';
        } else {
            $_SESSION['error_message'] = 'Đã xảy ra lỗi khi thêm địa chỉ.';
        }
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Xử lý Chọn Địa Chỉ Mặc Định
    if (isset($_POST['set_default'])) {
        $address_id = $_POST['address_id'];

        $sql = "UPDATE shipping_addresses SET is_default = 0 WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $sql = "UPDATE shipping_addresses SET is_default = 1 WHERE address_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $address_id);
        $stmt->execute();

        $_SESSION['success_message'] = 'Đã chọn địa chỉ mặc định thành công.';
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Xử lý Chỉnh Sửa Địa Chỉ
    if (isset($_POST['edit_address'])) {
        $address_id = $_POST['address_id'];
        $address = $_POST['address'];
        $recipient_name = $_POST['recipient_name'];
        $recipient_phone = $_POST['recipient_phone'];

        $sql = "UPDATE shipping_addresses SET address = ?, recipient_name = ?, recipient_phone = ? WHERE address_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $address, $recipient_name, $recipient_phone, $address_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Địa chỉ đã được cập nhật thành công.';
        } else {
            $_SESSION['error_message'] = 'Đã xảy ra lỗi khi cập nhật địa chỉ.';
        }
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Xử lý Xóa Địa Chỉ
    if (isset($_POST['delete_address'])) {
        $address_id = $_POST['address_id'];

        // Kiểm tra xem có đơn hàng liên kết
        $sql_check = "SELECT COUNT(*) AS count FROM orders WHERE address_id = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("i", $address_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $row_check = $result_check->fetch_assoc();

        if ($row_check['count'] > 0) {
            $_SESSION['error_message'] = 'Không thể xóa địa chỉ vì có đơn hàng liên kết.';
        } else {
            // Kiểm tra xem địa chỉ có phải là địa chỉ mặc định không
            $sql_default_check = "SELECT is_default FROM shipping_addresses WHERE address_id = ?";
            $stmt_default_check = $conn->prepare($sql_default_check);
            $stmt_default_check->bind_param("i", $address_id);
            $stmt_default_check->execute();
            $result_default_check = $stmt_default_check->get_result();
            $row_default_check = $result_default_check->fetch_assoc();

            // Xóa địa chỉ
            $sql_delete = "DELETE FROM shipping_addresses WHERE address_id = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param("i", $address_id);
            if ($stmt_delete->execute()) {
                if ($row_default_check['is_default']) {
                    // Chọn địa chỉ mặc định mới nếu địa chỉ bị xóa là địa chỉ mặc định
                    $sql_update_default = "SELECT address_id FROM shipping_addresses WHERE user_id = ? LIMIT 1";
                    $stmt_update_default = $conn->prepare($sql_update_default);
                    $stmt_update_default->bind_param("i", $user_id);
                    $stmt_update_default->execute();
                    $result_update_default = $stmt_update_default->get_result();

                    if ($result_update_default->num_rows > 0) {
                        $new_default_address = $result_update_default->fetch_assoc();
                        $new_default_address_id = $new_default_address['address_id'];

                        $sql_set_default = "UPDATE shipping_addresses SET is_default = 1 WHERE address_id = ?";
                        $stmt_set_default = $conn->prepare($sql_set_default);
                        $stmt_set_default->bind_param("i", $new_default_address_id);
                        $stmt_set_default->execute();
                    }
                }
                $_SESSION['success_message'] = 'Địa chỉ đã được xóa thành công.';
            } else {
                $_SESSION['error_message'] = 'Đã xảy ra lỗi khi xóa địa chỉ.';
            }
            $stmt_delete->close();
            $stmt_default_check->close();
        }
        $stmt_check->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
// Truy vấn để lấy danh sách địa chỉ của người dùng
$sql = "SELECT * FROM shipping_addresses WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$addresses = [];
while ($row = $result->fetch_assoc()) {
    $addresses[] = $row;
}

$stmt->close();
?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thiết lập địa chỉ</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="assets/css/styleinfo.css">
    <style>

    </style>
</head>

<body>
    <?php include 'header.php';
    include_once 'contact_button.php'; ?>
    <main class="container mt-5">
        <h2><i class="fa-solid fa-location-dot"></i> Danh Sách Địa Chỉ </h2>
        <div class="row">
            <?php foreach ($addresses as $address): ?>
                <div class="col-md-4 mb-4">
                    <div class="card <?php echo $address['is_default'] ? 'card-default' : ''; ?>">
                        <div class="card-body">
                            <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($address['recipient_name']); ?></p>
                            <p><strong>Điện thoại:</strong> <?php echo htmlspecialchars($address['recipient_phone']); ?></p>
                            <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($address['address']); ?></p>
                            <?php if ($address['is_default']): ?>
                                <p class="text-success">
                                    <strong>Địa chỉ mặc định</strong> <i class="fa-solid fa-check"></i>
                                </p>
                            <?php else: ?>
                                <form method="POST" action="">
                                    <input type="hidden" name="address_id" value="<?php echo $address['address_id']; ?>">
                                    <button type="submit" name="set_default" class="btn btn-primary">Chọn làm địa chỉ mặc định</button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" action="" class="mt-2">
                                <input type="hidden" name="address_id" value="<?php echo $address['address_id']; ?>">
                                <button type="button" class="btn btn-warning" onclick="openEditModal('<?php echo $address['address_id']; ?>', '<?php echo htmlspecialchars($address['address']); ?>', '<?php echo htmlspecialchars($address['recipient_name']); ?>', '<?php echo htmlspecialchars($address['recipient_phone']); ?>')">Chỉnh sửa</button>
                                <button type="button" class="btn btn-danger btn-delete" data-address-id="<?php echo $address['address_id']; ?>">Xóa</button>
                            </form>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <h2 class="mt-4">Thêm Địa Chỉ Mới</h2>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="address" class="form-label">Địa chỉ</label>
                <input type="text" class="form-control" id="address" name="address" required>
            </div>
            <div class="mb-3">
                <label for="recipient_name" class="form-label">Tên người nhận</label>
                <input type="text" class="form-control" id="recipient_name" name="recipient_name" required>
            </div>
            <div class="mb-3">
                <label for="recipient_phone" class="form-label">Số điện thoại</label>
                <input type="text" class="form-control" id="recipient_phone" name="recipient_phone" required>
            </div>
            <button type="submit" name="add_address" class="btn btn-primary">Thêm Địa Chỉ</button>
        </form>
    </main>

    <!-- Modal cho thông báo thành công -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">Thành Công</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="successMessage">
                    <!-- Thông báo thành công sẽ được đặt vào đây -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal cho thông báo lỗi -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Lỗi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="errorMessage">
                    <!-- Thông báo lỗi sẽ được đặt vào đây -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal xác nhận xóa -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Xác Nhận Xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn xóa địa chỉ này không? Hành động này không thể hoàn tác.
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" method="POST" action="">
                        <input type="hidden" name="address_id" id="deleteAddressId">
                        <button type="submit" name="delete_address" class="btn btn-danger">Xóa</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal chỉnh sửa địa chỉ -->
    <div class="modal fade" id="editAddressModal" tabindex="-1" aria-labelledby="editAddressModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAddressModalLabel">Chỉnh Sửa Địa Chỉ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="address_id" id="editAddressId">
                        <div class="mb-3">
                            <label for="editAddress" class="form-label">Địa chỉ</label>
                            <textarea id="editAddress" name="address" class="form-control" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editRecipientName" class="form-label">Người nhận</label>
                            <input type="text" id="editRecipientName" name="recipient_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="editRecipientPhone" class="form-label">Điện thoại</label>
                            <input type="text" id="editRecipientPhone" name="recipient_phone" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" name="edit_address" class="btn btn-primary">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script>
        $(document).ready(function() {
            // Hiển thị modal thông báo thành công nếu có
            <?php if (isset($_SESSION['success_message'])): ?>
                $('#successMessage').text('<?php echo $_SESSION['success_message']; ?>');
                $('#successModal').modal('show');
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            // Hiển thị modal thông báo lỗi nếu có
            <?php if (isset($_SESSION['error_message'])): ?>
                $('#errorMessage').text('<?php echo $_SESSION['error_message']; ?>');
                $('#errorModal').modal('show');
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            // Cập nhật modal xác nhận xóa
            $('.btn-delete').on('click', function() {
                var addressId = $(this).data('address-id');
                $('#deleteAddressId').val(addressId);
                $('#confirmDeleteModal').modal('show');
            });
        });

        function openEditModal(addressId, address, recipientName, recipientPhone) {
            $('#editAddressId').val(addressId);
            $('#editAddress').val(address);
            $('#editRecipientName').val(recipientName);
            $('#editRecipientPhone').val(recipientPhone);
            $('#editAddressModal').modal('show');
        }
    </script>

</body>

</html>