<?php
include_once 'dbconnect.php';

$online_query = "SELECT COUNT(*) AS online_users FROM online_users";
$online_result = $conn->query($online_query);
$online_data = $online_result->fetch_assoc();

echo $online_data['online_users']; // Trả về số người đang online
?>
