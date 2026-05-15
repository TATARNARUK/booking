<?php
session_start();
require_once 'db.php';

// บังคับล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->connect();

// 1. ดึงข้อมูลเบอร์โทรศัพท์ของ User ปัจจุบัน เพื่อเอาไปเทียบกับประวัติการจอง
$query_user = "SELECT phone FROM users WHERE id = :user_id LIMIT 1";
$stmt_user = $db->prepare($query_user);
$stmt_user->bindParam(':user_id', $_SESSION['user_id']);
$stmt_user->execute();
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

// 2. ดึงประวัติการจองของลูกค้ารายนี้ (เรียงจากล่าสุดไปเก่าสุด)
// ใช้ JOIN เพื่อดึงชื่อห้องและรูปภาพจากตาราง rooms มาด้วย
$query_bookings = "
    SELECT b.*, r.name AS room_name, r.image_url 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    WHERE b.customer_phone = :phone 
    ORDER BY b.created_at DESC
";
$stmt_bookings = $db->prepare($query_bookings);
$stmt_bookings->bindParam(':phone', $user['phone']);
$stmt_bookings->execute();
$bookings = $stmt_bookings->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการจองของฉัน - Supreme Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">🏨 Supreme Booking</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="index.php">หน้าแรก</a></li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white fw-bold active" href="#" data-bs-toggle="dropdown">
                            👤 สวัสดี, <?php echo htmlspecialchars($_SESSION['first_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item active" href="my_bookings.php">ประวัติการจอง</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">ออกจากระบบ</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="container my-5">
        <div class="row mb-4">
            <div class="col">
                <h2 class="fw-bold">📅 ประวัติการจองของฉัน</h2>
                <p class="text-muted">ตรวจสอบสถานะและรายละเอียดการเข้าพักของคุณได้ที่นี่</p>
            </div>
        </div>

        <?php if(count($bookings) > 0): ?>
            <div class="row g-4">
                <?php foreach($bookings as $booking): ?>
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="row g-0">
                                <div class="col-md-3">
                                    <img src="<?php echo htmlspecialchars($booking['image_url']); ?>" class="img-fluid rounded-start h-100" alt="Room Image" style="object-fit: cover; min-height: 200px;">
                                </div>
                                <div class="col-md-9">
                                    <div class="card-body d-flex flex-column h-100">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h4 class="card-title fw-bold text-primary"><?php echo htmlspecialchars($booking['room_name']); ?></h4>
                                            
                                            <!-- เช็คสถานะการจองเพื่อแสดงป้ายสีต่างๆ -->
                                            <?php if($booking['status'] == 'pending'): ?>
                                                <span class="badge bg-warning text-dark fs-6">รอการยืนยัน</span>
                                            <?php elseif($booking['status'] == 'confirmed'): ?>
                                                <span class="badge bg-success fs-6">ยืนยันแล้ว</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger fs-6">ยกเลิกแล้ว</span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="row mt-auto">
                                            <div class="col-sm-6 mb-2">
                                                <small class="text-muted d-block">วันเช็คอิน</small>
                                                <span class="fw-bold fs-5"><?php echo date('d / m / Y', strtotime($booking['check_in'])); ?></span>
                                            </div>
                                            <div class="col-sm-6 mb-2">
                                                <small class="text-muted d-block">วันเช็คเอาต์</small>
                                                <span class="fw-bold fs-5"><?php echo date('d / m / Y', strtotime($booking['check_out'])); ?></span>
                                            </div>
                                        </div>
                                        
                                        <hr class="my-2">
                                        
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <small class="text-muted">รหัสการจอง: #BK-<?php echo str_pad($booking['id'], 5, '0', STR_PAD_LEFT); ?></small>
                                            <span class="fs-5 fw-bold text-dark">ราคารวม: ฿<?php echo number_format($booking['total_price'], 2); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-light border text-center p-5 shadow-sm" role="alert">
                <h4 class="text-muted mb-3">คุณยังไม่มีประวัติการจองห้องพัก</h4>
                <a href="index.php" class="btn btn-primary btn-lg">ดูห้องพักทั้งหมด</a>
            </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>