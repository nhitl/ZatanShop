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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
