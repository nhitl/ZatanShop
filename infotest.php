<?php

include 'dbconnect.php'; // Kết nối cơ sở dữ liệu
session_start();

// Kiểm tra xem người dùng đã đăng nhập hay chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Chuyển hướng đến trang đăng nhập
    exit();
}

$user_id = $_SESSION['user_id']; // Lấy ID người dùng

// Lấy thông tin người dùng từ cơ sở dữ liệu
$sql_user = "SELECT full_name, email, phone_number FROM users WHERE user_id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_info = $stmt_user->get_result()->fetch_assoc();

// Lấy danh sách địa chỉ giao hàng
$sql_addresses = "SELECT * FROM shipping_addresses WHERE user_id = ?";
$stmt_addresses = $conn->prepare($sql_addresses);
$stmt_addresses->bind_param("i", $user_id);
$stmt_addresses->execute();
$addresses = $stmt_addresses->get_result();

$error_message = ""; // Biến để lưu thông báo lỗi

// Xử lý form thêm địa chỉ mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_address') {
    $recipient_name = $_POST['recipient_name'];
    $recipient_phone = $_POST['recipient_phone'];
    $address = $_POST['address'];

    $sql_add = "INSERT INTO shipping_addresses (user_id, recipient_name, recipient_phone, address, is_default) VALUES (?, ?, ?, ?, 0)";
    $stmt_add = $conn->prepare($sql_add);
    $stmt_add->bind_param("isss", $user_id, $recipient_name, $recipient_phone, $address);

    if ($stmt_add->execute()) {
        header('Location: info.php');
    } else {
        $error_message = "Có lỗi xảy ra khi thêm địa chỉ.";
    }
}

// Xử lý chỉnh sửa địa chỉ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_address') {
    $address_id = $_POST['address_id'];
    $recipient_name = $_POST['recipient_name'];
    $recipient_phone = $_POST['recipient_phone'];
    $address = $_POST['address'];

    $sql_edit = "UPDATE shipping_addresses SET recipient_name = ?, recipient_phone = ?, address = ? WHERE address_id = ? AND user_id = ?";
    $stmt_edit = $conn->prepare($sql_edit);
    $stmt_edit->bind_param("sssii", $recipient_name, $recipient_phone, $address, $address_id, $user_id);

    if ($stmt_edit->execute()) {
        header('Location: info.php');
    } else {
        $error_message = "Có lỗi xảy ra khi chỉnh sửa địa chỉ.";
    }
}

// Xử lý xóa địa chỉ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_address') {
    $address_id = $_POST['address_id'];

    $sql_delete = "DELETE FROM shipping_addresses WHERE address_id = ? AND user_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("ii", $address_id, $user_id);

    if ($stmt_delete->execute()) {
        header('Location: info.php');
    } else {
        if ($conn->errno === 1451) { // Lỗi ràng buộc khóa ngoại
            $_SESSION['error_message'] = "Đang có đơn hàng giao đến địa chỉ này, không thể xóa!";
        } else {
            $_SESSION['error_message'] = "Có lỗi xảy ra khi xóa địa chỉ.";
        }
    }
}

// Xử lý đặt địa chỉ mặc định
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'set_default') {
    $address_id = $_POST['address_id'];

    // Đặt tất cả các địa chỉ của người dùng là không mặc định
    $sql_unset_default = "UPDATE shipping_addresses SET is_default = 0 WHERE user_id = ?";
    $stmt_unset = $conn->prepare($sql_unset_default);
    $stmt_unset->bind_param("i", $user_id);
    $stmt_unset->execute();

    // Đặt địa chỉ đã chọn là mặc định
    $sql_set_default = "UPDATE shipping_addresses SET is_default = 1 WHERE address_id = ? AND user_id = ?";
    $stmt_set = $conn->prepare($sql_set_default);
    $stmt_set->bind_param("ii", $address_id, $user_id);

    if ($stmt_set->execute()) {
        header('Location: info.php');
    } else {
        $error_message = "Có lỗi xảy ra khi đặt địa chỉ mặc định.";
    }
}

// Xử lý đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_password') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];

    // Lấy mật khẩu hiện tại từ cơ sở dữ liệu
    $sql_get_password = "SELECT password FROM users WHERE user_id = ?";
    $stmt_get_password = $conn->prepare($sql_get_password);
    $stmt_get_password->bind_param("i", $user_id);
    $stmt_get_password->execute();
    $result = $stmt_get_password->get_result();
    $user = $result->fetch_assoc();

    // Kiểm tra mật khẩu cũ
    if (password_verify($old_password, $user['password'])) {
        // Cập nhật mật khẩu mới
        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql_update_password = "UPDATE users SET password = ? WHERE user_id = ?";
        $stmt_update_password = $conn->prepare($sql_update_password);
        $stmt_update_password->bind_param("si", $hashed_new_password, $user_id);

        if ($stmt_update_password->execute()) {
            echo "Mật khẩu đã được cập nhật thành công.";
        } else {
            $error_message = "Có lỗi xảy ra khi cập nhật mật khẩu.";
        }
    } else {
        $error_message = "Mật khẩu cũ không đúng.";
    }
}
// Kiểm tra và lấy thông báo lỗi từ session nếu có
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Xóa thông báo lỗi sau khi hiển thị
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin cá nhân</title>
    <!-- Bao gồm Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styleinfo.css">
</head>

<body>
    <?php
    include_once 'header.php';
    ?>
    <div class="container mt-5">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="list-group">
                    <a href="#info" class="list-group-item list-group-item-action active" id="info-tab">Thông tin cá nhân</a>
                    <a href="#password" class="list-group-item list-group-item-action" id="password-tab">Đổi mật khẩu</a>
                    <a href="#address" class="list-group-item list-group-item-action" id="address-tab">Địa chỉ giao hàng</a>
                    <a href="logout.php" class="list-group-item list-group-item-action text-danger" id="logout-link">Đăng xuất</a>
                </div>
            </div>
            <!-- Nội dung -->
            <div class="col-md-9">
                <!-- Thông tin cá nhân -->
                <div id="info" class="content-tab">
                    <h2>Thông tin cá nhân</h2>
                    <form action="info.php" method="POST">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Họ và tên:</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user_info['full_name']); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Số điện thoại:</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user_info['phone_number']); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Cập nhật</button>
                    </form>
                </div>

                <!-- Đổi mật khẩu -->
                <div id="password" class="content-tab" style="display:none;">
                    <h2>Đổi mật khẩu</h2>
                    <form action="info.php" method="POST">
                        <div class="mb-3">
                            <label for="old_password" class="form-label">Mật khẩu cũ:</label>
                            <input type="password" class="form-control" id="old_password" name="old_password">
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Mật khẩu mới:</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                        </div>
                        <input type="hidden" name="action" value="update_password">
                        <button type="submit" class="btn btn-primary">Cập nhật mật khẩu</button>
                    </form>
                </div>

                <!-- Địa chỉ giao hàng -->
                <div id="address" class="content-tab" style="display:none;">
                    <h2>Địa chỉ giao hàng</h2>
                    <!-- Thêm địa chỉ mới -->
                    <form action="info.php" method="POST">
                        <div class="mb-3">
                            <label for="recipient_name" class="form-label">Tên người nhận:</label>
                            <input type="text" class="form-control" id="recipient_name" name="recipient_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="recipient_phone" class="form-label">Số điện thoại:</label>
                            <input type="text" class="form-control" id="recipient_phone" name="recipient_phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Địa chỉ:</label>
                            <input type="text" class="form-control" id="address" name="address" required>
                        </div>
                        <input type="hidden" name="action" value="add_address">
                        <button type="submit" class="btn btn-primary">Thêm địa chỉ</button>
                    </form>

                    <!-- Danh sách địa chỉ -->
                    <h3 class="mt-4">Danh sách các địa chỉ nhận hàng</h3>
                    <div class="row">
                        <?php while ($address = $addresses->fetch_assoc()) : ?>
                            <div class="col-12">
                                <div class="card">
                                    <div class="row">
                                        <div class="col-12 card-body">
                                            <h1 class="card-title"><?php echo htmlspecialchars($address['recipient_name']); ?></h1>
                                            <p class="card-text"><?php echo htmlspecialchars($address['recipient_phone']); ?></p>
                                            <p class="card-text"><?php echo htmlspecialchars($address['address']); ?></p>
                                        </div>
                                        <div class="col-12 select">
                                            <div class="row">
                                                <div class="col-6 default-address">
                                                    <?php if ($address['is_default'] == 0) : ?>
                                                        <!-- Đặt địa chỉ mặc định -->
                                                        <form action="info.php" method="POST" style="display:inline;">
                                                            <input type="hidden" name="address_id" value="<?php echo $address['address_id']; ?>">
                                                            <input type="hidden" name="action" value="set_default">
                                                            <button type="submit" class="btn btn-sm btn-success">Chọn mặc định</button>
                                                        </form>
                                                    <?php else : ?>
                                                        <button class="btn btn-sm btn-secondary" disabled>Mặc định</button>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-6 edit-delete-address">
                                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editAddressModal" data-address-id="<?php echo $address['address_id']; ?>" data-recipient-name="<?php echo htmlspecialchars($address['recipient_name']); ?>" data-recipient-phone="<?php echo htmlspecialchars($address['recipient_phone']); ?>" data-address="<?php echo htmlspecialchars($address['address']); ?>">Chỉnh sửa</button>
                                                    <form action="info.php" method="POST" style="display:inline;">
                                                        <input type="hidden" name="address_id" value="<?php echo $address['address_id']; ?>">
                                                        <input type="hidden" name="action" value="delete_address">
                                                        <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                                    </form>
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal chỉnh sửa địa chỉ -->
    <div class="modal fade" id="editAddressModal" tabindex="-1" aria-labelledby="editAddressModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAddressModalLabel">Chỉnh sửa địa chỉ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="info.php" method="POST">
                        <div class="mb-3">
                            <label for="edit_recipient_name" class="form-label">Tên người nhận:</label>
                            <input type="text" class="form-control" id="edit_recipient_name" name="recipient_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_recipient_phone" class="form-label">Số điện thoại:</label>
                            <input type="text" class="form-control" id="edit_recipient_phone" name="recipient_phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_address" class="form-label">Địa chỉ:</label>
                            <input type="text" class="form-control" id="edit_address" name="address" required>
                        </div>
                        <input type="hidden" id="edit_address_id" name="address_id">
                        <input type="hidden" name="action" value="edit_address">
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal thông báo lỗi -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Thông báo lỗi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="errorMessage">
                    <!-- Thông báo lỗi sẽ được chèn vào đây -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <?php
    include_once 'footer.php';
    ?>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

    <script>
        // Chuyển đổi giữa các tab
        document.querySelectorAll('.list-group-item').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.content-tab').forEach(content => {
                    content.style.display = 'none';
                });
                document.querySelectorAll('.list-group-item').forEach(item => {
                    item.classList.remove('active');
                });
                document.querySelector(`#${this.id.replace('-tab', '')}`).style.display = 'block';
                this.classList.add('active');
            });
        });
        $(document).ready(function() {
            $('.list-group a').click(function(event) {
                event.preventDefault(); // Chặn hành động mặc định của liên kết
                // Ẩn tất cả các nội dung
                $('.content-tab').hide();
                // Hiển thị nội dung tương ứng với tab đã nhấp
                $($(this).attr('href')).show();
                // Thêm lớp 'active' vào tab được nhấp và bỏ lớp 'active' khỏi các tab khác
                $('.list-group a').removeClass('active');
                $(this).addClass('active');
            });

            // Hiển thị nội dung mặc định khi tải trang
            $('#info').show();
        });


        // Cập nhật dữ liệu cho modal chỉnh sửa địa chỉ
        var editAddressModal = document.getElementById('editAddressModal');
        editAddressModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var address_id = button.getAttribute('data-address-id');
            var recipient_name = button.getAttribute('data-recipient-name');
            var recipient_phone = button.getAttribute('data-recipient-phone');
            var address = button.getAttribute('data-address');

            var modal = editAddressModal.querySelector('form');
            modal.querySelector('#edit_address_id').value = address_id;
            modal.querySelector('#edit_recipient_name').value = recipient_name;
            modal.querySelector('#edit_recipient_phone').value = recipient_phone;
            modal.querySelector('#edit_address').value = address;
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var errorMessage = "<?php echo addslashes($error_message); ?>";
            if (errorMessage) {
                var errorModal = new bootstrap.Modal(document.getElementById('errorModal'), {
                    keyboard: false
                });
                document.getElementById('errorMessage').textContent = errorMessage;
                errorModal.show();
            }
        });
    </script>

</body>

</html>