<?php
include_once '../dbconnect.php';
$result = $conn->query("SELECT * FROM voucher");
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý voucher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            margin-top: 80px;
        }

        .card {
            margin-bottom: 30px;
            /* Tăng khoảng cách dưới mỗi card */
        }

        .card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card-footer {
            display: flex;
            justify-content: flex-end;
        }

        .voucher-actions a {
            margin-left: 10px;
        }

        /* Thay đổi màu cho nút btn-primary mb-3 */
        .btn-primary.mb-3 {
            background-color: #ff6f00;
            /* Màu nền cam */
            border-color: #ff6f00;
            /* Màu viền cam */
        }

        .btn-primary.mb-3:hover {
            background-color: #e65c00;
            /* Màu nền cam tối hơn khi hover */
            border-color: #e65c00;
            /* Màu viền cam tối hơn khi hover */
        }

        .btn-primary.mb-3:focus,
        .btn-primary.mb-3:active {
            box-shadow: 0 0 0 0.2rem rgba(255, 111, 0, 0.5);
            /* Hiệu ứng bóng khi nút được nhấn hoặc focus */
        }

        /* Đảm bảo mô tả không vượt quá 3 dòng */
        .card-text-head {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            /* Số dòng tối đa */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            /* Hiển thị '...' nếu nội dung vượt quá */
            min-height: calc(1.2em * 3);
            /* Chiều cao tối thiểu bằng 3 dòng, điều chỉnh theo font-size */
            line-height: 1.2em;
            /* Điều chỉnh line-height phù hợp */
        }
    </style>
</head>

<body>
    <?php
    // Include header
    include_once '../header.php';
    include_once '../notification.php';
    ?>
    <div class="container">
        <h1 class="text-center">Danh sách Voucher</h1>
        <a href="add_voucher.php" class="btn btn-primary mb-3">Thêm Voucher mới</a>

        <div class="row">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Mã Voucher: <?php echo $row['voucher_code']; ?></h5>
                            <p class="card-text-head"><strong>Mô tả:</strong> <?php echo $row['description']; ?></p>
                            <p class="card-text"><strong>Phần trăm giảm:</strong>
                                <?php echo rtrim(rtrim(number_format($row['discount_percentage'], 2), '0'), '.'); ?>%</p>
                            <p class="card-text"><strong>Ngày hết hạn:</strong> <?php echo $row['expiry_date']; ?></p>
                            <p class="card-text"><strong>Giá trị đơn tối thiểu:</strong>
                                <?php echo rtrim(rtrim(number_format($row['min_order_value'], 2), '0'), '.'); ?></p>
                            <p class="card-text"><strong>Giá trị giảm tối đa:</strong>
                                <?php echo rtrim(rtrim(number_format($row['max_discount_value'], 2), '0'), '.'); ?></p>
                            <p class="card-text"><strong>Trạng thái:</strong> <?php echo $row['status']; ?></p>
                        </div>
                        <div class="card-footer voucher-actions">
                            <a href="edit_voucher.php?id=<?php echo $row['voucher_id']; ?>" class="btn btn-warning">Sửa</a>
                            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-voucher-id="<?php echo $row['voucher_id']; ?>">Xóa</button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
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
                <div class="modal-body">
                    Bạn có chắc chắn muốn xóa voucher này không?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <a id="confirm-delete" href="#" class="btn btn-danger">Xóa</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript để xử lý sự kiện xóa
        var deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget; // Nút Xóa
            var voucherId = button.getAttribute('data-voucher-id');
            var deleteUrl = 'delete_voucher.php?id=' + voucherId;

            var confirmDelete = document.getElementById('confirm-delete');
            confirmDelete.setAttribute('href', deleteUrl);
        });
    </script>
</body>

</html>