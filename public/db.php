<?php
$servername = "localhost";
$username = "root";
$password = ""; // 如果有設定密碼，就填進來
$dbname = "abc"; // ← 這裡就是資料庫名稱

// 建立連線
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連線
if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}
?>
