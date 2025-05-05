
<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>預約系統</title>
</head>
<body>
    <h2>預約表單</h2>
    <form method="POST" action="">
        <label>姓名：</label><br>
        <input type="text" name="name" required><br><br>

        <label>電話：</label><br>
        <input type="tel" name="phone" pattern="[0-9]{10}" placeholder="例如：0912345678" required><br><br>

        <label>預約日期：</label><br>
        <input type="date" name="date" required><br><br>

        <label>預約時段：</label><br>
        <select name="time_slot" required>
            <option value="09:00 - 10:00">09:00 - 10:00</option>
            <option value="10:00 - 11:00">10:00 - 11:00</option>
            <option value="11:00 - 12:00">11:00 - 12:00</option>
            <!-- 可自由新增 -->
        </select><br><br>

        <label>人數：</label><br>
        <input type="number" name="people_count" min="1" required><br><br>

        <button type="submit" name="submit">送出預約</button>
    </form>

    <?php
    if (isset($_POST['submit'])) {
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $date = $_POST['date'];
        $time_slot = $_POST['time_slot'];
        $people_count = intval($_POST['people_count']);

        // 插入資料，新增了 date 欄位
        $stmt = $conn->prepare("INSERT INTO reservations (name, phone, date, time_slot, people_count) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $name, $phone, $date, $time_slot, $people_count);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>預約成功！</p>";
        } else {
            echo "<p style='color: red;'>預約失敗：" . $stmt->error . "</p>";
        }

        $stmt->close();
    }

    $conn->close();
    ?>
</body>
</html>

