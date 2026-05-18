<?php
require_once 'lang.php';
/** @var string $current_lang */
require_once 'db.php';

// บังคับล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->connect();

$message = '';
$status = '';

// รับค่า room_id
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

// ข้อมูล 2 ภาษา
$name_key = 'name_' . $current_lang;
$desc_key = 'description_' . $current_lang;
$display_name = !empty($room[$name_key]) ? $room[$name_key] : $room['name_th'];
$display_desc = !empty($room[$desc_key]) ? $room[$desc_key] : $room['description_th'];

// ดึงข้อมูลผู้ใช้
$query_user = "SELECT * FROM users WHERE id = :user_id LIMIT 1";
$stmt_user = $db->prepare($query_user);
$stmt_user->bindParam(':user_id', $_SESSION['user_id']);
$stmt_user->execute();
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

// จัดการเมื่อกดยืนยันการจอง
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $customer_name = $user['first_name'] . ' ' . $user['last_name'];
    $customer_phone = $user['phone'];

    $datetime1 = new DateTime($check_in);
    $datetime2 = new DateTime($check_out);
    $interval = $datetime1->diff($datetime2);
    $days = $interval->days;

    if ($days > 0) {
        $total_price = $days * $room['price'];

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
    <title><?php echo htmlspecialchars($display_name); ?> - Supreme Booking</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Inter', 'Prompt', sans-serif; background-color: #f7f9fa; }
        .navbar-custom { background-color: #ffffff !important; box-shadow: 0 1px 15px rgba(0,0,0,0.05); padding: 15px 0; }
        .navbar-custom .nav-link { color: #222222 !important; font-weight: 600; }
        .hero-image { width: 100%; height: 500px; object-fit: cover; border-radius: 24px; margin-bottom: 30px; }
        .feature-icon { width: 40px; height: 40px; background-color: #f8f9fa; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-size: 1.2rem; }
        .sticky-booking-card { position: sticky; top: 100px; z-index: 10; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-danger fs-4" href="index.php">
                <i class="bi bi-geo-alt-fill"></i> Supreme Booking
            </a>
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
        <div class="row g-5">
            
            <div class="col-lg-8">
                <img src="<?php echo htmlspecialchars($room['image_url']); ?>" class="hero-image shadow-sm" alt="Room Image">
                
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h1 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($display_name); ?></h1>
                </div>
                
                <div class="d-flex gap-4 text-muted mb-4 border-bottom pb-4">
                    <span><i class="bi bi-people-fill me-1"></i> ผู้เข้าพักสูงสุด <?php echo $room['capacity']; ?> ท่าน</span>
                    <span><i class="bi bi-door-open-fill me-1"></i> 1 ห้องนอน 1 ห้องน้ำ</span>
                    <span><i class="bi bi-star-fill text-warning me-1"></i> 4.9 (128 รีวิว)</span>
                </div>

                <h4 class="fw-bold mb-3">รายละเอียดที่พัก</h4>
                <p class="text-muted" style="line-height: 1.8; font-size: 1.05rem;">
                    <?php echo nl2br(htmlspecialchars($display_desc)); ?>
                </p>

                <hr class="my-5">

                <h4 class="fw-bold mb-4">สิ่งอำนวยความสะดวกยอดนิยม</h4>
                <div class="row g-4 mb-4">
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="feature-icon text-primary"><i class="bi bi-wifi"></i></div>
                        <span class="fs-5 text-dark">Wi-Fi ฟรีความเร็วสูง</span>
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="feature-icon text-success"><i class="bi bi-car-front"></i></div>
                        <span class="fs-5 text-dark">ที่จอดรถส่วนตัวฟรี</span>
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="feature-icon text-info"><i class="bi bi-water"></i></div>
                        <span class="fs-5 text-dark">สระว่ายน้ำกลางแจ้ง</span>
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="feature-icon text-danger"><i class="bi bi-cup-hot"></i></div>
                        <span class="fs-5 text-dark">รวมอาหารเช้า</span>
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="feature-icon text-secondary"><i class="bi bi-tv"></i></div>
                        <span class="fs-5 text-dark">สมาร์ททีวี 55 นิ้ว (Netflix)</span>
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="feature-icon text-primary"><i class="bi bi-snow"></i></div>
                        <span class="fs-5 text-dark">เครื่องปรับอากาศทุกห้อง</span>
                    </div>
                </div>

                <hr class="my-5">
                
                <h4 class="fw-bold mb-4">ข้อควรรู้ก่อนเข้าพัก</h4>
                <div class="bg-light rounded-4 p-4">
                    <div class="row">
                        <div class="col-sm-6 mb-3 mb-sm-0">
                            <h6 class="fw-bold"><i class="bi bi-clock me-2"></i> เช็คอิน / เช็คเอาต์</h6>
                            <p class="text-muted mb-0 small">เช็คอินตั้งแต่: 14:00 น.<br>เช็คเอาต์ก่อน: 12:00 น.</p>
                        </div>
                        <div class="col-sm-6">
                            <h6 class="fw-bold"><i class="bi bi-shield-x me-2"></i> นโยบายการยกเลิก</h6>
                            <p class="text-muted mb-0 small">ยกเลิกฟรีภายใน 48 ชั่วโมงหลังจากทำการจอง หากยกเลิกหลังจากนั้นจะมีการหักค่าธรรมเนียม</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-lg rounded-4 p-4 sticky-booking-card">
                    
                    <div class="d-flex align-items-end mb-4">
                        <h2 class="fw-bold mb-0 text-dark" id="roomPrice" data-price="<?php echo $room['price']; ?>">
                            ฿<?php echo number_format($room['price'], 0); ?>
                        </h2>
                        <span class="text-muted ms-2 mb-1">/ <?php echo __('night'); ?></span>
                    </div>

                    <?php if($message != ''): ?>
                        <div class="alert alert-<?php echo $status == 'success' ? 'success' : 'danger'; ?> rounded-4 text-center py-3">
                            <i class="bi bi-check-circle-fill fs-1 d-block mb-2"></i>
                            <strong><?php echo $message; ?></strong>
                            <?php if($status == 'success'): ?>
                                <br><a href="my_bookings.php" class="btn btn-outline-success rounded-pill mt-3 w-100">ดูประวัติการจอง</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if($status != 'success'): ?>
                        <form action="book_room.php?room_id=<?php echo $room_id; ?>" method="POST">
                            
                            <div class="border rounded-4 p-3 mb-3">
                                <label class="form-label text-muted small fw-bold mb-1">ชื่อผู้จอง</label>
                                <input type="text" class="form-control bg-transparent border-0 p-0 fw-bold text-dark" value="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>" readonly>
                            </div>

                            <div class="d-flex border rounded-4 mb-4 overflow-hidden">
                                <div class="w-50 p-3 border-end">
                                    <label class="form-label text-muted small fw-bold mb-1">เช็คอิน</label>
                                    <input type="date" name="check_in" id="checkIn" class="form-control bg-transparent border-0 p-0" min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="w-50 p-3">
                                    <label class="form-label text-muted small fw-bold mb-1">เช็คเอาต์</label>
                                    <input type="date" name="check_out" id="checkOut" class="form-control bg-transparent border-0 p-0" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-danger w-100 py-3 fs-5 fw-bold rounded-pill shadow-sm mb-4 transition-hover" id="btnSubmit" disabled>จองห้องพัก</button>

                            <div class="d-flex justify-content-between text-muted mb-2">
                                <span>฿<?php echo number_format($room['price'], 0); ?> x <span id="totalNights">0</span> คืน</span>
                                <span>฿<span id="subTotal">0</span></span>
                            </div>
                            <div class="d-flex justify-content-between text-muted mb-3 border-bottom pb-3">
                                <span>ค่าธรรมเนียมบริการ</span>
                                <span>฿0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fs-5 fw-bold text-dark">ยอดรวมทั้งหมด</span>
                                <span class="fs-4 fw-bold text-dark">฿<span id="totalPrice">0</span></span>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
    </div>

    <script>
        const checkInInput = document.getElementById('checkIn');
        const checkOutInput = document.getElementById('checkOut');
        const totalNightsSpan = document.getElementById('totalNights');
        const subTotalSpan = document.getElementById('subTotal');
        const totalPriceSpan = document.getElementById('totalPrice');
        const btnSubmit = document.getElementById('btnSubmit');
        const roomPrice = parseFloat(document.getElementById('roomPrice').getAttribute('data-price'));

        function calculatePrice() {
            const checkInDate = new Date(checkInInput.value);
            const checkOutDate = new Date(checkOutInput.value);

            if(checkInInput.value) {
                let minCheckOut = new Date(checkInDate);
                minCheckOut.setDate(minCheckOut.getDate() + 1);
                checkOutInput.min = minCheckOut.toISOString().split('T')[0];
            }

            if (checkInInput.value && checkOutInput.value) {
                const timeDifference = checkOutDate.getTime() - checkInDate.getTime();
                const nightCount = Math.ceil(timeDifference / (1000 * 3600 * 24));

                if (nightCount > 0) {
                    totalNightsSpan.textContent = nightCount;
                    const total = nightCount * roomPrice;
                    subTotalSpan.textContent = total.toLocaleString('en-US');
                    totalPriceSpan.textContent = total.toLocaleString('en-US');
                    
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = "จองห้องพักเลย";
                } else {
                    resetSummary();
                }
            } else {
                resetSummary();
            }
        }

        function resetSummary() {
            totalNightsSpan.textContent = "0";
            subTotalSpan.textContent = "0";
            totalPriceSpan.textContent = "0";
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = "โปรดเลือกวันเข้าพัก";
        }

        checkInInput.addEventListener('change', calculatePrice);
        checkOutInput.addEventListener('change', calculatePrice);
        resetSummary(); // ล็อกปุ่มไว้ตั้งแต่เริ่ม
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>