<?php
session_start();
require_once 'db.php';

// 🛡️ ระบบความปลอดภัย: ตรวจสอบว่าเป็น Admin หรือไม่
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('เฉพาะผู้ดูแลระบบเท่านั้นที่เข้าถึงหน้านี้ได้'); window.location.href='index.php';</script>";
    exit();
}

$database = new Database();
$db = $database->connect();

// ดึงการจองทั้งหมด พร้อมชื่อห้องพัก
$query = "SELECT b.*, r.name AS room_name 
          FROM bookings b 
          JOIN rooms r ON b.room_id = r.id 
          ORDER BY b.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$all_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                <a class="nav-link" href="admin_dashboard.php">จัดการการจอง</a>
                <a class="nav-link text-white" href="admin_rooms.php">จัดการห้องพัก</a> <a class="nav-link text-white" href="index.php">กลับหน้าเว็บหลัก</a>
                <a class="nav-link text-white" href="logout.php">ออกจากระบบ</a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h3 class="mb-0 fw-bold text-dark">รายการจองห้องพักทั้งหมด</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>ห้องพัก</th>
                                <th>ผู้จอง</th>
                                <th>เบอร์โทร</th>
                                <th>เช็คอิน / เช็คเอาต์</th>
                                <th>ยอดรวม</th>
                                <th>สถานะ</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_bookings as $b): ?>
                                <tr>
                                    <td>#<?php echo $b['id']; ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($b['room_name']); ?></td>
                                    <td><?php echo htmlspecialchars($b['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($b['customer_phone']); ?></td>
                                    <td>
                                        <small class="d-block text-muted">In: <?php echo $b['check_in']; ?></small>
                                        <small class="d-block text-muted">Out: <?php echo $b['check_out']; ?></small>
                                    </td>
                                    <td class="fw-bold">฿<?php echo number_format($b['total_price'], 2); ?></td>
                                    <td>
                                        <?php if ($b['status'] == 'pending'): ?>
                                            <span class="badge bg-warning text-dark">รอการยืนยัน</span>
                                        <?php elseif ($b['status'] == 'confirmed'): ?>
                                            <span class="badge bg-success">ยืนยันแล้ว</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">ยกเลิก</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="update_status.php?id=<?php echo $b['id']; ?>&status=confirmed" class="btn btn-sm btn-outline-success" title="ยืนยันการจอง"><i class="bi bi-check-lg"></i></a>
                                            <a href="update_status.php?id=<?php echo $b['id']; ?>&status=cancelled" class="btn btn-sm btn-outline-danger" title="ยกเลิกการจอง"><i class="bi bi-x-lg"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>