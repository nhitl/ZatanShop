<?php
if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show alert-custom" role="alert">
        <?= $_SESSION['success_message']; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['success_message']); // Xóa thông báo sau khi hiển thị ?>
<?php endif; ?>
