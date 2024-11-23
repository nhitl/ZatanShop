<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$vnp_TmnCode = "A477Z1E7"; // Mã website (Terminal ID)
$vnp_HashSecret = "LWIO1UKE8JMYVPP72VQL74UZEPMI3HHK"; // Chuỗi bí mật (Secret Key)
$vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html"; // URL thanh toán môi trường test
$vnp_Returnurl = "http://localhost:8081/TEST/vnpay_return.php"; // URL xử lý kết quả thanh toán
$vnp_apiUrl = "http://sandbox.vnpayment.vn/merchant_webapi/merchant.html";
$apiUrl = "https://sandbox.vnpayment.vn/merchant_webapi/api/transaction";
//Config input format
//Expire
$startTime = date("YmdHis");
$expire = date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));
