<?php
$servername = "d0c7lphr0fns73e50730-a.oregon-postgres.render.com";
$username = "reservations_sql_user";
$password = "WBC5ij6U1fp7c6Q1DR8B4VrCNyDdD2OR"; // 如果有設定密碼，就填進來
$dbname = "reservations_sql"; // ← 這裡就是資料庫名稱

// 建立連線
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連線
if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}
?>
