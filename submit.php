<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $phone = $_POST["phone"];
    $date = $_POST['date'];
    $time_slot = $_POST['time_slot'];
    $people_count = intval($_POST['people_count']);

    $stmt = $conn->prepare("INSERT INTO reservations (name, phone, date, time_slot, people_count) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $name, $phone, $date, $time_slot, $people_count);


    if ($stmt->execute()) {
        echo "預約成功！";
    } else {
        echo "錯誤：" . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?><br><br>
<form action="2.html" method="get">
        <button type="submit">返回</button>
 </form>
