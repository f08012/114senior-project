<?php
session_start();
date_default_timezone_set("Asia/Taipei");

// 固定座位表
$fixedSeats = [
    ['id' => "1", 'size' => 4, 'occupied' => false, 'start_time' => null],
    ['id' => "2", 'size' => 4, 'occupied' => false, 'start_time' => null],
    ['id' => "3", 'size' => 4, 'occupied' => false, 'start_time' => null],
    ['id' => "4", 'size' => 4, 'occupied' => false, 'start_time' => null],
    ['id' => "5", 'size' => 4, 'occupied' => false, 'start_time' => null],
    ['id' => "6", 'size' => 2, 'occupied' => false, 'start_time' => null],
    ['id' => "7", 'size' => 2, 'occupied' => false, 'start_time' => null],
    ['id' => "8", 'size' => 2, 'occupied' => false, 'start_time' => null],
    ['id' => "9", 'size' => 2, 'occupied' => false, 'start_time' => null],
    ['id' => "10", 'size' => 2, 'occupied' => false, 'start_time' => null]
];

if (!isset($_SESSION['seats'])) {
    $_SESSION['seats'] = $fixedSeats;
}

$assignedSeats = [];
$waitingMessage = null;
$now = time();

function findAdjacentSeats($size, &$remainingPeople) {
    global $_SESSION, $now;
    $seats = &$_SESSION['seats'];
    $assigned = [];

    if ($remainingPeople == 5 || $remainingPeople == 6) {
        $combos = [[0, 5], [1, 6], [2, 7], [3, 8], [4, 9]];
        foreach ($combos as $combo) {
            if (!$seats[$combo[0]]['occupied'] && !$seats[$combo[1]]['occupied']) {
                $assigned[] = $seats[$combo[0]]['id'];
                $assigned[] = $seats[$combo[1]]['id'];
                $seats[$combo[0]]['occupied'] = true;
                $seats[$combo[1]]['occupied'] = true;
                $seats[$combo[0]]['start_time'] = $now;
                $seats[$combo[1]]['start_time'] = $now;
                $remainingPeople -= 6;
                break;
            }
        }
    }

    if ($remainingPeople > 0) {
        for ($i = 0; $i < count($seats); $i++) {
            if ($seats[$i]['occupied'] || $seats[$i]['size'] != $size) continue;
            $group = [];
            $count = 0;
            for ($j = $i; $j < count($seats) && $count < $remainingPeople; $j++) {
                if (!$seats[$j]['occupied'] && $seats[$j]['size'] == $size) {
                    $group[] = &$seats[$j];
                    $count += $seats[$j]['size'];
                } else {
                    break;
                }
            }
            if ($count >= $remainingPeople) {
                foreach ($group as &$seat) {
                    $seat['occupied'] = true;
                    $seat['start_time'] = $now;
                    $assigned[] = $seat['id'];
                    $remainingPeople -= $seat['size'];
                    if ($remainingPeople <= 0) break;
                }
                break;
            }
        }
    }

    return $assigned;
}

// 安排座位請求
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['people'])) {
    $people = intval($_POST['people']);
    $remainingPeople = $people;

    if ($remainingPeople == 1 || $remainingPeople == 2) {
        $assignedSeats = findAdjacentSeats(2, $remainingPeople);
    }
    if ($remainingPeople > 0) {
        $assignedSeats = array_merge($assignedSeats, findAdjacentSeats(4, $remainingPeople));
    }
    if ($remainingPeople > 0) {
        $assignedSeats = array_merge($assignedSeats, findAdjacentSeats(2, $remainingPeople));
    }
    if ($remainingPeople > 0) {
        $waitingMessage = "目前無足夠的鄰近座位，請稍等。";
    }
       // ✅ 安排完座位後重新導回首頁
       header("Location: " . $_SERVER['PHP_SELF']);
       exit;
}

// 清空單一座位
if (isset($_POST['clear_seat'])) {
    foreach ($_SESSION['seats'] as &$seat) {
        if ($seat['id'] == $_POST['clear_seat']) {
            $seat['occupied'] = false;
            $seat['start_time'] = null;
            break;
        }
    }
    unset($seat);
}

// 處理刪除預約
include 'db.php';
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $conn->query("DELETE FROM reservations WHERE id = $delete_id");
}
// 處理安排座位
if (isset($_GET['assign'])) {
    $assign_id = intval($_GET['assign']);
    $res = $conn->query("SELECT * FROM reservations WHERE id = $assign_id");
    if ($res && $res->num_rows > 0) {
        $reservation = $res->fetch_assoc();
        $people = intval($reservation['people_count']);
        $remainingPeople = $people;
        $assignedSeats = [];

        if ($remainingPeople == 1 || $remainingPeople == 2) {
            $assignedSeats = findAdjacentSeats(2, $remainingPeople);
        }
        if ($remainingPeople > 0) {
            $assignedSeats = array_merge($assignedSeats, findAdjacentSeats(4, $remainingPeople));
        }
        if ($remainingPeople > 0) {
            $assignedSeats = array_merge($assignedSeats, findAdjacentSeats(2, $remainingPeople));
        }

        if (!empty($assignedSeats)) {
            $seatAssignedStr = implode(",", $assignedSeats);
            $conn->query("UPDATE reservations SET seat_assigned = '$seatAssignedStr' WHERE id = $assign_id");
        }
    }

    // ⭐⭐ 安排完座位後跳轉回首頁
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>餐廳座位安排</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script>
    setTimeout(function(){
      location.reload();
    }, 10000); // 每10秒刷新
  </script>
</head>
<body class="container py-4">

<h1 class="mb-4">餐廳座位表</h1>

<form method="POST" action="" class="mb-3">
    <label class="form-label">輸入人數：</label>
    <input type="number" name="people" class="form-control" required>
    <button type="submit" class="btn btn-primary mt-2">安排座位</button>
</form>

<?php if (!empty($assignedSeats)): ?>
    <div class="alert alert-success">已安排到桌號: <?= implode(", ", $assignedSeats) ?></div>
<?php endif; ?>

<?php if ($waitingMessage): ?>
    <div class="alert alert-warning"><?= $waitingMessage ?></div>
<?php endif; ?>

<?php
usort($_SESSION['seats'], function ($a, $b) {
    $order = [1,2,3,4,5,6,7,8,9,10];
    return array_search((int)$a['id'], $order) - array_search((int)$b['id'], $order);
});

$seats = $_SESSION['seats'];
?>

<!-- 第一排 -->
<div class="row mt-4">
<?php for ($i = 0; $i < 5; $i++): $seat = $seats[$i]; ?>
    <div class="col-md-2 p-2">
        <div class="card text-center <?= $seat['occupied'] ? 'bg-warning' : 'bg-light' ?>">
            <div class="card-body">
                <h5 class="card-title">桌號: <?= $seat['id'] ?></h5>
                <p>人數: <?= $seat['size'] ?></p>
                <p>狀態: 
                <?php 
                if ($seat['occupied']) {
                    $endTime = strtotime("+2 hours", $seat['start_time']);
                    echo $now >= $endTime ? '<span class="text-danger">用餐時間到了</span>' : '用餐時間到 ' . date("H:i", $endTime);
                } else {
                    echo '可用';
                }
                ?></p>
                <?php if ($seat['occupied']): ?>
                    <form method="POST">
                        <button type="submit" name="clear_seat" value="<?= $seat['id'] ?>" class="btn btn-danger">清空座位</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endfor; ?>
</div>

<!-- 第二排 -->
<div class="row mt-3">
<?php for ($i = 5; $i < 10; $i++): $seat = $seats[$i]; ?>
    <div class="col-md-2 p-2">
        <div class="card text-center <?= $seat['occupied'] ? 'bg-warning' : 'bg-light' ?>">
            <div class="card-body">
                <h5 class="card-title">桌號: <?= $seat['id'] ?></h5>
                <p>人數: <?= $seat['size'] ?></p>
                <p>狀態: 
                <?php 
                if ($seat['occupied']) {
                    $endTime = strtotime("+2 hours", $seat['start_time']);
                    echo $now >= $endTime ? '<span class="text-danger">用餐時間到了</span>' : '用餐時間到 ' . date("H:i", $endTime);
                } else {
                    echo '可用';
                }
                ?></p>
                <?php if ($seat['occupied']): ?>
                    <form method="POST">
                        <button type="submit" name="clear_seat" value="<?= $seat['id'] ?>" class="btn btn-danger">清空座位</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endfor; ?>
</div>

<hr class="my-5">

<h2>預約紀錄列表</h2>

<?php
$sql = "SELECT * FROM reservations WHERE seat_assigned IS NULL ORDER BY date ASC, time_slot ASC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table class='table table-bordered'>";
    echo "<thead class='table-dark'>
            <tr>
              <th>姓名</th>
              <th>電話</th>
              <th>預約時段</th>
              <th>預約日期</th>
              <th>人數</th>
              <th>建立時間</th>
              <th>操作</th>
              <th>安排座位</th>
            </tr>
          </thead><tbody>";

    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($row["name"]) . "</td>
                <td>" . htmlspecialchars($row["phone"]) . "</td>
                <td>" . htmlspecialchars($row["time_slot"]) . "</td>
                <td>" . htmlspecialchars($row["date"]) . "</td> <!-- 注意這裡是date -->
                <td>" . htmlspecialchars($row["people_count"]) . "</td>
                <td>" . htmlspecialchars($row["created_at"]) . "</td>
                <td>" . htmlspecialchars($row["seat_assigned"]) . "</td>
                <td>
  <a href='?assign=" . $row["id"] . "' class='btn btn-success btn-sm' onclick=\"return confirm('確定要安排座位嗎？');\">安排座位</a>
  <a href='?delete=" . $row["id"] . "' class='btn btn-danger btn-sm' onclick=\"return confirm('確定要刪除這筆預約嗎？');\">刪除</a>
</td>
              </tr>";
    }

    echo "</tbody></table>";
} else {
    echo "<p>目前沒有預約紀錄。</p>";
}
?>

</body>
</html>
