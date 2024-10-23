<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin tức công nghệ</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .news{
            padding-top: 20px;
        }
    </style>
</head>

<body>
    <?php
    include_once 'dbconnect.php';
    include_once 'header.php';
    include_once 'contact_button.php';
    ?>
    <section class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tin tức</li>
                </ol>
            </nav>
        </div>
    </section>
    <section class="news">
        <h2 class="text-center mb-4">TẤT CẢ TIN TỨC CÔNG NGHỆ</h2>
        <div class="container">
            <?php
            // Truy vấn để lấy tất cả các tin tức từ CSDL
            $query = "SELECT news_id, news_name, news_image FROM news ORDER BY news_id DESC";
            $result = mysqli_query($conn, $query);

            // Kiểm tra và hiển thị tin tức nếu có dữ liệu
            if ($result && mysqli_num_rows($result) > 0) {
                echo '<div class="row">';

                // Hiển thị từng tin tức
                while ($row = mysqli_fetch_assoc($result)) {
                    // Lấy đường dẫn ảnh từ cơ sở dữ liệu
                    $imagePath = $row["news_image"];
                    
                    // Tạo đường dẫn chính xác từ thư mục gốc của dự án
                    $fullImagePath = 'admin/admin/' . $imagePath;
                    
                    // Hiển thị ảnh
                    echo '<div class="col-lg-4 col-md-6 col-sm-6 mb-4">';
                    echo '    <div class="blog__item">';
                    echo '        <a href="blog-details.php?news_id=' . $row['news_id'] . '">';
                    echo '            <img src="' . $fullImagePath . '" alt="' . $row["news_name"] . '" class="img-fluid">';
                    echo '        </a>';
                    echo '        <div class="blog__item__text">';
                    echo '            <h5>' . $row['news_name'] . '</h5>';
                    echo '            <a href="blog-details.php?news_id=' . $row['news_id'] . '">Xem Ngay</a>';
                    echo '        </div>';
                    echo '    </div>';
                    echo '</div>';
                }
                
                
                
                

                echo '</div>';

                // Giải phóng bộ nhớ
                mysqli_free_result($result);
            } else {
                // Hiển thị thông báo nếu không có tin tức
                echo '<p>Không có tin tức nào.</p>';
            }
            ?>
        </div>
    </section>
    <!-- Footer -->
    <?php
    include_once 'footer.php';
    ?>

</body>

</html>
