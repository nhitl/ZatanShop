<?php
session_start();
include 'dbconnect.php'; // Kết nối database

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    die("Bạn chưa đăng nhập!");
}

$currentUserId = $_SESSION['user_id']; // Lấy user_id của người dùng hiện tại

// Xử lý cập nhật trạng thái hoặc xóa thông báo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['action'])) {
    $notificationId = intval($_POST['id']);

    if ($_POST['action'] === 'update') {
        $sql = "UPDATE notification_orders SET status = 'read' WHERE id = ? AND user_id = ?";
    } elseif ($_POST['action'] === 'delete') {
        $sql = "DELETE FROM notification_orders WHERE id = ? AND user_id = ?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $notificationId, $currentUserId);
    echo ($stmt->execute()) ? 'success' : 'error';
    exit;
}

// Lấy danh sách thông báo của người dùng hiện tại
$stmtNotifications = $conn->prepare("SELECT * FROM notification_orders WHERE user_id = ? ORDER BY created_at DESC");
$stmtNotifications->bind_param("i", $currentUserId);
$stmtNotifications->execute();
$resultNotifications = $stmtNotifications->get_result();
$notifications = $resultNotifications->fetch_all(MYSQLI_ASSOC);

// Đếm số lượng thông báo của user hiện tại
$stmtTotalCount = $conn->prepare("SELECT COUNT(*) as count FROM notification_orders WHERE user_id = ?");
$stmtTotalCount->bind_param("i", $currentUserId);
$stmtTotalCount->execute();
$totalCount = $stmtTotalCount->get_result()->fetch_assoc()['count'];

$stmtUnreadCount = $conn->prepare("SELECT COUNT(*) as count FROM notification_orders WHERE user_id = ? AND status = 'unread'");
$stmtUnreadCount->bind_param("i", $currentUserId);
$stmtUnreadCount->execute();
$unreadCount = $stmtUnreadCount->get_result()->fetch_assoc()['count'];

$stmtReadCount = $conn->prepare("SELECT COUNT(*) as count FROM notification_orders WHERE user_id = ? AND status = 'read'");
$stmtReadCount->bind_param("i", $currentUserId);
$stmtReadCount->execute();
$readCount = $stmtReadCount->get_result()->fetch_assoc()['count'];

?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="assets/css/notifi-order.css">
</head>

<body>
    <?php include_once 'header.php'; ?>
    <section class="notifi-order">
        <div class="container mt-4">
            <h3>📢 Thông báo của bạn</h3>
            <ul class="nav nav-tabs" id="notificationTabs">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#all">Tất cả (<?= $totalCount; ?>)</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#unread">Chưa đọc (<?= $unreadCount; ?>)</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#read">Đã đọc (<?= $readCount; ?>)</a></li>
            </ul>


            <div class="tab-content mt-3">
                <!-- Tab Tất cả -->
                <div id="all" class="tab-pane fade show active">
                    <?php foreach ($notifications as $noti): ?>
                        <div class="notification-item <?= ($noti['status'] == 'unread') ? 'notification-unread' : 'notification-read'; ?>" data-id="<?= $noti['id']; ?>">
                            <strong>📝 <?= htmlspecialchars($noti['message']); ?></strong>
                            <span class="text-muted d-block"><?= date("d/m/Y H:i", strtotime($noti['created_at'])); ?></span>
                            <span class="delete-btn" data-id="<?= $noti['id']; ?>">❌</span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Tab Chưa đọc -->
                <div id="unread" class="tab-pane fade">
                    <?php foreach ($notifications as $noti): ?>
                        <?php if ($noti['status'] == 'unread'): ?>
                            <div class="notification-item notification-unread" data-id="<?= $noti['id']; ?>">
                                <strong>📝 <?= htmlspecialchars($noti['message']); ?></strong>
                                <span class="text-muted d-block"><?= date("d/m/Y H:i", strtotime($noti['created_at'])); ?></span>
                                <span class="delete-btn" data-id="<?= $noti['id']; ?>">❌</span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <!-- Tab Đã đọc -->
                <div id="read" class="tab-pane fade">
                    <?php foreach ($notifications as $noti): ?>
                        <?php if ($noti['status'] == 'read'): ?>
                            <div class="notification-item notification-read" data-id="<?= $noti['id']; ?>">
                                <strong>📝 <?= htmlspecialchars($noti['message']); ?></strong>
                                <span class="text-muted d-block"><?= date("d/m/Y H:i", strtotime($noti['created_at'])); ?></span>
                                <span class="delete-btn" data-id="<?= $noti['id']; ?>">❌</span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <?php include_once 'footer.php'; ?>

    <div class="modal fade" id="notificationModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered"> <!-- Thêm class này -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chi tiết thông báo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="notificationContent"></p>
                </div>
            </div>
        </div>
    </div>


    <script>
        $(document).ready(function() {
            // Khi bấm vào thông báo
            $('.notification-item').click(function() {
                let notiId = $(this).data('id');
                let message = $(this).find('strong').text();

                // Hiển thị nội dung trong modal
                $('#notificationContent').text(message);
                $('#notificationModal').modal('show');

                // Cập nhật trạng thái thành "đã đọc"
                $(this).removeClass('notification-unread').addClass('notification-read');

                // Nếu đang ở tab "Chưa đọc", di chuyển thông báo sang tab "Đã đọc"
                if ($('#unread').hasClass('show')) {
                    let $this = $(this);
                    setTimeout(function() {
                        $this.appendTo('#read'); // Di chuyển vào tab "Đã đọc"
                    }, 300);
                }

                $.post('thongbao.php', {
                    id: notiId,
                    action: 'update'
                });
            });



            // Xóa thông báo
            $('.delete-btn').click(function(e) {
                e.stopPropagation();
                let notiId = $(this).data('id');
                let $noti = $('[data-id="' + notiId + '"]');

                if (confirm("Bạn có chắc chắn muốn xóa thông báo này?")) {
                    $.post('thongbao.php', {
                        id: notiId,
                        action: 'delete'
                    }, function(response) {
                        if (response === 'success') {
                            $noti.fadeOut(function() {
                                $(this).remove();
                            });
                        }
                    });
                }
            });
        });
        $('#notificationModal').on('hidden.bs.modal', function() {
            location.reload(); // Tải lại trang khi modal đóng
        });
    </script>
</body>

</html>