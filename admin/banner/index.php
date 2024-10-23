<?php
// Kết nối CSDL
include_once '../dbconnect.php';

// Truy vấn dữ liệu banners
$sql = "SELECT * FROM banners";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banners</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../assets/css/modal.css">
</head>

<body>
    <?php
    // Include file header và thông báo
    include_once '../header.php';
    include_once '../notification.php';
    ?>

    <section class="banner">
        <div class="container">
            <h1 class="title text-center my-5">Danh sách Banners</h1>

            <div class="gap-2 mb-3">
                <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Thêm Banner mới</a>
            </div>

            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="col">
                            <div class="card h-100">
                                <div class="position-relative">
                                    <h5 class="card-title text-center position-absolute top-0 w-100 text-white bg-dark bg-opacity-50 py-2"><?= $row['banner_name'] ?></h5>
                                    <img src="<?= $row['banner_image'] ?>" class="img-banner" alt="<?= $row['banner_name'] ?>">
                                </div>
                                <div class="card-footer">
                                    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#viewModal<?= $row['banner_id'] ?>"><i class="fas fa-eye"></i> Xem chi tiết</button>
                                    <a href="edit.php?id=<?= $row['banner_id'] ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Sửa</a>
                                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['banner_id'] ?>"><i class="fas fa-trash"></i> Xóa</button>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Xem Chi Tiết -->
                        <div class="modal fade modal-banner" id="viewModal<?= $row['banner_id'] ?>" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="viewModalLabel">Chi tiết Banner</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <h5 class="text-center mb-4"><?= $row['banner_name'] ?></h5>
                                        <div class="text-center mb-3">
                                            <img src="<?= $row['banner_image'] ?>" class="img-fluid rounded" alt="<?= $row['banner_name'] ?>">
                                        </div>
                                        <div class="mb-3">
                                            <h6>Nội dung:</h6>
                                            <p><?= $row['content'] ?></p>
                                        </div>
                                        <div class="mb-3">
                                            <h6>Liên kết:</h6>
                                            <p><a href="<?= $row['link'] ?>" target="_blank"><?= $row['link'] ?></a></p>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Xóa -->
                        <div class="modal fade modal-deletebanner" id="deleteModal<?= $row['banner_id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteModalLabel">Xác nhận xóa</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Bạn có chắc chắn muốn xóa banner "<strong><?= $row['banner_name'] ?></strong>"?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                        <a href="delete.php?id=<?= $row['banner_id'] ?>" class="btn btn-danger">Xóa</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col">
                        <p class="text-center">Không có banner nào.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$conn->close();
?>
