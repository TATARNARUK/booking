<?php
require_once 'lang.php';
/** @var string $current_lang */
require_once 'db.php';

// 1. ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->connect();

// 2. ดึงเบอร์โทรของลูกค้าที่ล็อกอินอยู่ เพื่อใช้เทียบกับการจอง
$stmt_user = $db->prepare("SELECT phone FROM users WHERE id = :id LIMIT 1");
$stmt_user->bindParam(':id', $_SESSION['user_id']);
$stmt_user->execute();
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);
$user_phone = $user['phone'];

// 3. ดึงประวัติการจองของลูกค้ารายนี้ พร้อมดึงรูปและชื่อห้องมาด้วย
$query = "SELECT b.*, r.name_th, r.name_en, r.image_url 
          FROM bookings b 
          LEFT JOIN rooms r ON b.room_id = r.id 
          WHERE b.customer_phone = :phone 
          ORDER BY b.id DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':phone', $user_phone);
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('my_bookings'); ?> - Supreme Booking</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Inter', 'Prompt', sans-serif;
            background-color: #f7f9fa;
        }

        .navbar-custom {
            background-color: #ffffff !important;
            box-shadow: 0 1px 15px rgba(0, 0, 0, 0.05);
            padding: 15px 0;
        }

        .navbar-custom .nav-link {
            color: #222222 !important;
            font-weight: 600;
        }

        .booking-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            transition: transform 0.2s;
            overflow: hidden;
        }

        .booking-card:hover {
            transform: translateY(-3px);
        }

        .room-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        @media (min-width: 768px) {
            .room-img {
                height: 100%;
                min-height: 220px;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-danger fs-4" href="index.php">
                <i class="bi bi-geo-alt-fill"></i> Supreme Booking
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-3">
                        <div class="dropdown">
                            <button class="btn btn-light rounded-pill dropdown-toggle border" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-globe2"></i> <?php echo strtoupper($current_lang); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                <li><a class="dropdown-item" href="?lang=th">🇹🇭 ภาษาไทย</a></li>
                                <li><a class="dropdown-item" href="?lang=en">🇬🇧 English</a></li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle bg-light rounded-pill px-3 py-2 border" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-5 text-secondary align-middle me-1"></i>
                            <?php echo __('welcome') . ', ' . htmlspecialchars($_SESSION['first_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 rounded-4">
                            <?php if ($_SESSION['role'] == 'admin'): ?>
                                <li><a class="dropdown-item fw-bold" href="admin_dashboard.php"><?php echo __('admin_panel'); ?></a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                            <?php endif; ?>
                            <li><a class="dropdown-item active" href="my_bookings.php"><?php echo __('my_bookings'); ?></a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><?php echo __('logout'); ?></a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h2 class="fw-bold mb-4">🧳 ทริปเดินทางของคุณ (My Trips)</h2>

        <?php if (count($bookings) > 0): ?>
            <div class="row g-4">
                <?php foreach ($bookings as $b):
                    // เช็คภาษาของชื่อห้อง
                    $name_key = 'name_' . $current_lang;
                    $room_name = !empty($b[$name_key]) ? $b[$name_key] : $b['name_th'];
                ?>
                    <div class="col-12">
                        <div class="card booking-card bg-white">
                            <div class="row g-0">
                                <div class="col-md-3">
                                    <img src="<?php echo htmlspecialchars($b['image_url']); ?>" class="room-img" alt="Room Image">
                                </div>
                                <div class="col-md-9">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h4 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($room_name); ?></h4>
                                                <p class="text-muted small mb-0">รหัสการจอง: #BKG-<?php echo str_pad($b['id'], 4, '0', STR_PAD_LEFT); ?></p>
                                            </div>
                                            <?php if ($b['status'] == 'pending'): ?>
                                                <span class="badge bg-warning text-dark rounded-pill px-3 py-2"><i class="bi bi-hourglass-split"></i> รอการชำระเงิน</span>
                                            <?php elseif ($b['status'] == 'paid'): ?>
                                                <span class="badge bg-info text-dark rounded-pill px-3 py-2"><i class="bi bi-currency-dollar"></i> รอแอดมินตรวจสอบ</span>
                                            <?php elseif ($b['status'] == 'confirmed'): ?>
                                                <span class="badge bg-success rounded-pill px-3 py-2"><i class="bi bi-check-circle"></i> ยืนยันการจองสำเร็จ</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger rounded-pill px-3 py-2"><i class="bi bi-x-circle"></i> ถูกยกเลิก</span>
                                            <?php endif; ?>
                                        </div>

                                        <hr class="text-muted my-3">

                                        <div class="row g-3">
                                            <div class="col-sm-4">
                                                <label class="text-muted small fw-bold mb-1">วันที่เช็คอิน</label>
                                                <div class="fs-5 text-dark"><i class="bi bi-calendar-event me-2"></i><?php echo date('d M Y', strtotime($b['check_in'])); ?></div>
                                            </div>
                                            <div class="col-sm-4">
                                                <label class="text-muted small fw-bold mb-1">วันที่เช็คเอาต์</label>
                                                <div class="fs-5 text-dark"><i class="bi bi-calendar-check me-2"></i><?php echo date('d M Y', strtotime($b['check_out'])); ?></div>
                                            </div>
                                            <div class="col-sm-4 text-sm-end">
                                                <label class="text-muted small fw-bold mb-1">ยอดชำระทั้งหมด</label>
                                                <div class="fs-4 fw-bold text-danger">฿<?php echo number_format($b['total_price'], 0); ?></div>
                                            </div>
                                        </div>

                                        <div class="mt-4 pt-3 border-top d-flex gap-2 justify-content-end">
                                            <button class="btn btn-outline-secondary rounded-pill px-4" disabled>พิมพ์ใบเสร็จ</button>
                                            <?php if ($b['status'] == 'pending'): ?>
                                                <a href="upload_slip.php?booking_id=<?php echo $b['id']; ?>" class="btn btn-primary rounded-pill px-4"><i class="bi bi-cloud-upload"></i> อัปโหลดสลิป</a>
                                            <?php else: ?>
                                                <button class="btn btn-outline-secondary rounded-pill px-4" disabled><i class="bi bi-file-earmark-check"></i> ส่งสลิปแล้ว</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5 mt-5 bg-white rounded-4 shadow-sm border">
                <i class="bi bi-luggage text-muted" style="font-size: 5rem;"></i>
                <h3 class="fw-bold mt-3">คุณยังไม่มีประวัติการเดินทาง</h3>
                <p class="text-muted mb-4">ถึงเวลาออกไปสำรวจโลกกว้างและสร้างความทรงจำใหม่ๆ แล้ว!</p>
                <a href="index.php" class="btn btn-danger btn-lg rounded-pill px-5 fw-bold">ค้นหาที่พักเลย</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>