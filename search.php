<?php
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['phone'])) {
    $phone = $_POST['phone'];
    $conn = new mysqli("localhost", "root", "", "abc");
    if ($conn->connect_error) {
        die("連線失敗: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT name, date, time_slot, people_count FROM reservations WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='8'><tr><th>姓名</th><th>日期</th><th>時段</th><th>人數</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['name']}</td><td>{$row['date']}</td><td>{$row['time_slot']}</td><td>{$row['people_count']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>查無預約資料。</p>";
    }

    $stmt->close();
    $conn->close();
}
?>