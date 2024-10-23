<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết tin tức</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }


        /* Định dạng tiêu đề tin tức */
        .news-details h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Định dạng hình ảnh của tin tức */
        .news-details img {
            max-width: 800px;
            /* Chiều rộng tối đa của hình ảnh là 800px */
            width: 100%;
            /* Đảm bảo hình ảnh chiếm toàn bộ chiều rộng của phần tử chứa (nếu nhỏ hơn 800px) */
            height: auto;
            /* Giữ tỷ lệ khung hình của hình ảnh */
            display: block;
            /* Xóa khoảng trắng phía dưới hình ảnh */
            margin: 0 auto;
            /* Căn giữa hình ảnh */
            border-radius: 8px;
            /* Bo góc hình ảnh */
            margin-bottom: 20px;
            /* Khoảng cách phía dưới hình ảnh */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            /* Đổ bóng nhẹ cho hình ảnh */
        }


        /* Định dạng nội dung tin tức */
        .news-details p {
            font-size: 1.2rem;
            color: #333;
            text-align: justify;
            margin-bottom: 20px;
        }

        /* Định dạng cho các liên kết */
        a {
            color: #ff6600;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #333;
        }

        @media (max-width: 768px) {
            .news-details h2 {
                font-size: 2rem;
            }

            .news-details p {
                font-size: 1.1rem;
            }

            .container {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <?php
    include_once 'dbconnect.php';
    include_once 'header.php';
    include_once 'contact_button.php';

    // Lấy ID của tin tức từ URL
    if (isset($_GET['news_id'])) {
        $news_id = $_GET['news_id'];

        // Truy vấn để lấy chi tiết tin tức từ CSDL
        $query = "SELECT * FROM news WHERE news_id = $news_id";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);

            echo '<section class="news-details">';
            echo '<div class="container">';
            echo '<h2 class="text-center mb-4">' . $row['news_name'] . '</h2>';
            echo '<img src="admin/' . $row['news_image'] . '" alt="' . $row['news_name'] . '" class="img-fluid mb-4">';
            // Sử dụng nl2br để giữ nguyên các ký tự xuống dòng trong content
            echo '<p>' . nl2br($row['content']) . '</p>';
            echo '</div>';
            echo '</section>';
        } else {
            echo '<p>Không tìm thấy tin tức.</p>';
        }
    } else {
        echo '<p>ID tin tức không hợp lệ.</p>';
    }
    ?>

    <!-- Footer -->
    <?php
    include_once 'footer.php';
    ?>


</body>

</html>