<?php
require_once 'lang.php'; // โหลดระบบภาษา
/** @var string $current_lang */ // ซ่อนเส้นแดงแจ้งเตือนใน VS Code

require_once 'db.php';

// 1. บังคับล็อกอิน: ถ้ายังไม่ล็อกอินให้ไปหน้า login ก่อน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->connect();

$message = '';
$status = '';

// 2. รับค่า room_id จาก URL
if (!isset($_GET['room_id']) || empty($_GET['room_id'])) {
    header("Location: index.php");
    exit();
}

$room_id = $_GET['room_id'];

// ดึงข้อมูลห้องพัก
$query_room = "SELECT * FROM rooms WHERE id = :room_id LIMIT 1";
$stmt_room = $db->prepare($query_room);
$stmt_room->bindParam(':room_id', $room_id);
$stmt_room->execute();
$room = $stmt_room->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    echo "ไม่พบข้อมูลห้องพัก";
    exit();
}

// เตรียมข้อมูล 2 ภาษาสำหรับแสดงผล
$name_key = 'name_' . $current_lang;
$desc_key = 'description_' . $current_lang;
$display_name = !empty($room[$name_key]) ? $room[$name_key] : $room['name_th'];
$display_desc = !empty($room[$desc_key]) ? $room[$desc_key] : $room['description_th'];

// ดึงข้อมูลผู้ใช้ปัจจุบัน (เอาชื่อและเบอร์โทรมาใส่ฟอร์มอัตโนมัติ)
$query_user = "SELECT * FROM users WHERE id = :user_id LIMIT 1";
$stmt_user = $db->prepare($query_user);
$stmt_user->bindParam(':user_id', $_SESSION['user_id']);
$stmt_user->execute();
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

// 3. จัดการเมื่อผู้ใช้กดยืนยันการจอง (POST Request)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $customer_name = $user['first_name'] . ' ' . $user['last_name'];
    $customer_phone = $user['phone'];

    // คำนวณจำนวนคืนและราคาฝั่ง Server
    $datetime1 = new DateTime($check_in);
    $datetime2 = new DateTime($check_out);
    $interval = $datetime1->diff($datetime2);
    $days = $interval->days;

    if ($days > 0) {
        $total_price = $days * $room['price'];

        // บันทึกลงตาราง bookings
        $insert_booking = "INSERT INTO bookings (room_id, customer_name, customer_phone, check_in, check_out, total_price) 
                           VALUES (:room_id, :customer_name, :customer_phone, :check_in, :check_out, :total_price)";
        
        $stmt_booking = $db->prepare($insert_booking);
        $stmt_booking->bindParam(':room_id', $room_id);
        $stmt_booking->bindParam(':customer_name', $customer_name);
        $stmt_booking->bindParam(':customer_phone', $customer_phone);
        $stmt_booking->bindParam(':check_in', $check_in);
        $stmt_booking->bindParam(':check_out', $check_out);
        $stmt_booking->bindParam(':total_price', $total_price);

        if ($stmt_booking->execute()) {
            $status = 'success';
            $message = '🎉 จองห้องพักสำเร็จ! ระบบได้บันทึกข้อมูลของคุณแล้ว';
        } else {
            $status = 'error';
            $message = 'เกิดข้อผิดพลาดในการจอง โปรดลองอีกครั้ง';
        }
    } else {
        $status = 'error';
        $message = 'วันเช็คเอาต์ ต้องอยู่หลังวันเช็คอินอย่างน้อย 1 วันครับ';
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยันการจอง - <?php echo __('site_title'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Inter', 'Prompt', sans-serif; background-color: #f7f9fa; }
        .navbar-custom { background-color: #ffffff !important; box-shadow: 0 1px 15px rgba(0,0,0,0.05); padding: 15px 0; }
        .navbar-custom .nav-link { color: #222222 !important; font-weight: 600; }
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
                                <li><a class="dropdown-item" href="?room_id=<?php echo $room_id; ?>&lang=th">🇹🇭 ภาษาไทย</a></li>
                                <li><a class="dropdown-item" href="?room_id=<?php echo $room_id; ?>&lang=en">🇬🇧 English</a></li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle bg-light rounded-pill px-3 py-2 border" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-5 text-secondary align-middle me-1"></i> 
                            <?php echo __('welcome') . ', ' . htmlspecialchars($_SESSION['first_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 rounded-4">
                            <?php if($_SESSION['role'] == 'admin'): ?>
                                <li><a class="dropdown-item fw-bold" href="admin_dashboard.php"><?php echo __('admin_panel'); ?></a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="my_bookings.php"><?php echo __('my_bookings'); ?></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><?php echo __('logout'); ?></a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row">
            <div class="col-md-5 mb-4">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                    <img src="<?php echo htmlspecialchars($room['image_url']); ?>" class="card-img-top" alt="Room Image" style="height: 350px; object-fit: cover;">
                    <div class="card-body p-4">
                        <h3 class="card-title fw-bold"><?php echo htmlspecialchars($display_name); ?></h3>
                        <p class="text-muted"><?php echo htmlspecialchars($display_desc); ?></p>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fs-5 text-muted">ราคาต่อคืน:</span>
                            <span class="fs-4 fw-bold text-dark" id="roomPrice" data-price="<?php echo $room['price']; ?>">
                                ฿<?php echo number_format($room['price'], 0); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card border-0 shadow rounded-4">
                    <div class="card-header bg-white py-4 border-bottom-0">
                        <h4 class="mb-0 fw-bold">ระบุวันที่ต้องการเข้าพัก</h4>
                    </div>
                    <div class="card-body p-4">

                        <?php if($message != ''): ?>
                            <div class="alert alert-<?php echo $status == 'success' ? 'success' : 'danger'; ?> rounded-4" role="alert">
                                <?php echo $message; ?>
                                <?php if($status == 'success'): ?>
                                    <br><a href="index.php" class="btn btn-outline-success rounded-pill mt-3 px-4">กลับหน้าแรก</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if($status != 'success'): ?>
                            <form action="book_room.php?room_id=<?php echo $room_id; ?>" method="POST">
                                
                                <div class="mb-4">
                                    <label class="form-label text-muted">ชื่อผู้จอง (ดึงจากระบบอัตโนมัติ)</label>
                                    <input type="text" class="form-control bg-light border-0 py-2" value="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>" readonly>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label class="form-label fw-bold">วันเช็คอิน</label>
                                        <input type="date" name="check_in" id="checkIn" class="form-control py-2" min="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">วันเช็คเอาต์</label>
                                        <input type="date" name="check_out" id="checkOut" class="form-control py-2" required>
                                    </div>
                                </div>

                                <div class="alert bg-light border rounded-4 p-4 d-flex justify-content-between align-items-center mb-4">
                                    <span class="fs-5 text-muted">สรุปยอดชำระ (<span id="totalNights" class="fw-bold text-dark">0</span> คืน)</span>
                                    <span class="fs-2 fw-bold text-danger">฿<span id="totalPrice">0</span></span>
                                </div>

                                <button type="submit" class="btn btn-danger w-100 py-3 fs-5 fw-bold rounded-pill shadow-sm" id="btnSubmit" disabled>ยืนยันการจองห้องพัก</button>
                            </form>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const checkInInput = document.getElementById('checkIn');
        const checkOutInput = document.getElementById('checkOut');
        const totalNightsSpan = document.getElementById('totalNights');
        const totalPriceSpan = document.getElementById('totalPrice');
        const btnSubmit = document.getElementById('btnSubmit');
        const roomPrice = parseFloat(document.getElementById('roomPrice').getAttribute('data-price'));

        function calculatePrice() {
            const checkInDate = new Date(checkInInput.value);
            const checkOutDate = new Date(checkOutInput.value);

            // เซ็ตให้วันเช็คเอาต์ต้องเลือกหลังวันเช็คอินเสมอ
            if(checkInInput.value) {
                let minCheckOut = new Date(checkInDate);
                minCheckOut.setDate(minCheckOut.getDate() + 1);
                checkOutInput.min = minCheckOut.toISOString().split('T')[0];
            }

            // ถ้าเลือกครบ 2 วันแล้ว ให้คำนวณราคา
            if (checkInInput.value && checkOutInput.value) {
                const timeDifference = checkOutDate.getTime() - checkInDate.getTime();
                const nightCount = Math.ceil(timeDifference / (1000 * 3600 * 24));

                if (nightCount > 0) {
                    totalNightsSpan.textContent = nightCount;
                    // แปลงตัวเลขให้มีคอมม่า (เช่น 2,500)
                    totalPriceSpan.textContent = (nightCount * roomPrice).toLocaleString('en-US', {minimumFractionDigits: 0});
                    btnSubmit.disabled = false; // เปิดปุ่มให้กดจองได้
                } else {
                    totalNightsSpan.textContent = "0";
                    totalPriceSpan.textContent = "0";
                    btnSubmit.disabled = true;
                }
            }
        }

        checkInInput.addEventListener('change', calculatePrice);
        checkOutInput.addEventListener('change', calculatePrice);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>