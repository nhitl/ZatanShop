<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css" />

    <style>
        /* CSS TÙY CHỈNH CHO NProgress */
        #nprogress .bar {
            height: 4px; /* Tăng chiều cao */
            background: #dab166; /* Màu của thanh loading */
        }

        #nprogress .peg {
            box-shadow: 0 0 10px orange, 0 0 5px orange; /* Hiệu ứng ánh sáng */
        }

        #nprogress .spinner {
            display: none; /* Ẩn biểu tượng xoay */
        }
    </style>
</head>
<body>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>

    <script>
        // Bắt đầu thanh loading khi bắt đầu tải trang
        window.addEventListener('load', function() {
            NProgress.start(); // bắt đầu loading

            // Dừng thanh loading khi trang hoàn tất
            NProgress.done();
        });

        // Khi điều hướng giữa các trang, bạn có thể kích hoạt lại
        document.addEventListener('turbolinks:request-start', function() {
            NProgress.start();
        });

        document.addEventListener('turbolinks:load', function() {
            NProgress.done();
        });
    </script>

</body>
</html>
