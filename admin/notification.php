<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Khởi tạo session nếu chưa có
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/modal.css">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Thông báo thành công -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="overlay" id="overlay"></div>
        <div class="success-icon" id="successIcon">
            <div class="border-wrapper"></div>
            <div class="icon-wrapper">
                <i class="fa-solid fa-check"></i>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var successIcon = document.getElementById('successIcon');
                var overlay = document.getElementById('overlay');

                successIcon.classList.add('show');
                overlay.classList.add('show');

                setTimeout(function() {
                    successIcon.classList.remove('show');
                    overlay.classList.remove('show');
                }, 2000); // 2 giây
            });
        </script>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <!-- Thông báo lỗi (Modal) -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="errorModalLabel">Lỗi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <i class="fa-solid fa-exclamation-triangle" style="color: red;"></i>
                        <span><?php echo $_SESSION['error_message']; ?></span>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                errorModal.show();
            });
        </script>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

</body>
</html>
