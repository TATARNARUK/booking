<?php
session_start();
require_once 'db.php';

// 🛡️ เช็คสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->connect();

// ดึงข้อมูลห้องพักทั้งหมด
$query = "SELECT * FROM rooms ORDER BY id DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ระบบลบห้องพัก
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $del_query = "DELETE FROM rooms WHERE id = :id";
    $del_stmt = $db->prepare($del_query);
    $del_stmt->bindParam(':id', $id);
    if ($del_stmt->execute()) {
        header("Location: admin_rooms.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Supreme Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-danger shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="admin_dashboard.php"><i class="bi bi-speedometer2"></i> Admin Control Panel</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link text-white" href="admin_dashboard.php">จัดการการจอง</a>
                <a class="nav-link" href="admin_rooms.php">จัดการห้องพัก</a> <a class="nav-link text-white" href="index.php">กลับหน้าเว็บหลัก</a>
                <a class="nav-link text-white" href="logout.php">ออกจากระบบ</a>
            </div>
        </div>
    </nav>
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">🛏️ จัดการห้องพัก</h2>
            <a href="admin_room_add.php" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-plus-lg"></i> เพิ่มห้องพักใหม่
            </a>
        </div>

        <div class="card table-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">รูปภาพ</th>
                                <th>ชื่อห้องพัก</th>
                                <th>ราคา/คืน</th>
                                <th>ความจุ</th>
                                <th class="text-end pe-4">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rooms as $room): ?>
                                <tr class="align-middle">
                                    <td class="ps-4">
                                        <img src="<?php echo htmlspecialchars($room['image_url']); ?>" class="rounded-3" style="width: 80px; height: 60px; object-fit: cover;">
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($room['name_th']); ?></div>
                                        <small class="text-muted d-block text-truncate" style="max-width: 250px;">
                                            <?php echo htmlspecialchars($room['description_th']); ?>
                                        </small>
                                    </td>
                                    <td class="fw-bold">฿<?php echo number_format($room['price'], 0); ?></td>
                                    <td><i class="bi bi-people me-1"></i> <?php echo $room['capacity']; ?> ท่าน</td>
                                    <td class="text-end pe-4">
                                        <a href="admin_room_edit.php?id=<?php echo $room['id']; ?>" class="btn btn-sm btn-outline-secondary rounded-pill me-1">แก้ไข</a>
                                        <a href="?delete=<?php echo $room['id']; ?>" class="btn btn-sm btn-outline-danger rounded-pill" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบห้องนี้?')">ลบ</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>

</html>