<?php
require_once 'lang.php';
/** @var string $current_lang */
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->connect();

$message = '';
$status_msg = '';

if (!isset($_GET['booking_id'])) {
    header("Location: my_bookings.php");
    exit();
}

$booking_id = $_GET['booking_id'];

// ดึงข้อมูลการจองนี้มาแสดงตรวจสอบ
$query = "SELECT b.*, r.name_th, r.name_en 
          FROM bookings b 
          JOIN rooms r ON b.room_id = r.id 
          WHERE b.id = :booking_id LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindParam(':booking_id', $booking_id);
$stmt->execute();
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    echo "ไม่พบข้อมูลการจอง";
    exit();
}

// อัปโหลดไฟล์เมื่อมี Post Request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['slip_image'])) {
    $file = $_FILES['slip_image'];

    // ตรวจสอบความผิดพลาดของไฟล์
    if ($file['error'] === 0) {
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];

        // เช็คว่าเป็นไฟล์รูปภาพจริงไหม
        if (in_array($file_ext, $allowed_ext)) {
            // เช็คขนาดไฟล์ (ไม่เกิน 5MB)
            if ($file['size'] <= 5 * 1024 * 1024) {

                // ตั้งชื่อไฟล์ใหม่แบบสุ่มเพื่อไม่ให้ชื่อซ้ำกัน
                $new_file_name = "SLIP_" . uniqid() . "_" . time() . "." . $file_ext;
                $upload_dir = 'uploads/slips/';

                // เช็คว่ามีโฟลเดอร์ slips หรือยัง ถ้ายังไม่มีให้สร้างขึ้นมาใหม่พร้อมให้สิทธิ์ (777)
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $upload_destination = $upload_dir . $new_file_name;

                if (move_uploaded_file($file['tmp_name'], $upload_destination)) {

                    // อัปเดตข้อมูลลงฐานข้อมูล เปลี่ยนสถานะเป็น 'paid' (รอตรวจสอบ)
                    $update_query = "UPDATE bookings SET payment_slip = :slip, status = 'paid' WHERE id = :id";
                    $update_stmt = $db->prepare($update_query);
                    $update_stmt->bindParam(':slip', $new_file_name);
                    $update_stmt->bindParam(':id', $booking_id);

                    if ($update_stmt->execute()) {
                        $status_msg = 'success';
                        $message = '🚀 อัปโหลดสลิปสำเร็จ! ระบบกำลังตรวจสอบยอดเงินของคุณ';
                        // รีเฟรชข้อมูลหน้าเว็บใหม่
                        header("Refresh: 2; url=my_bookings.php");
                    }
                } else {
                    $status_msg = 'danger';
                    $message = 'เกิดข้อผิดพลาดในการย้ายไฟล์ไปยังโฟลเดอร์ปลายทาง';
                }
            } else {
                $status_msg = 'danger';
                $message = 'ขนาดไฟล์ใหญ่เกินไป จำกัดไม่เกิน 5MB ครับ';
            }
        } else {
            $status_msg = 'danger';
            $message = 'อนุญาตเฉพาะไฟล์รูปภาพประเภท JPG, JPEG และ PNG เท่านั้นครับ';
        }
    } else {
        $status_msg = 'danger';
        $message = 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์';
    }
}

$room_name = ($current_lang == 'en' && !empty($booking['name_en'])) ? $booking['name_en'] : $booking['name_th'];
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">

<head>
    <meta charset="UTF-8">
    <title>แจ้งชำระเงิน - Supreme Booking</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f7f9fa;
        }

        .upload-box {
            border: 2px dashed #ccc;
            border-radius: 15px;
            background: #fff;
            text-align: center;
            padding: 40px 20px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .upload-box:hover {
            border-color: #ff385c;
            background: #fff5f6;
        }
    </style>
</head>

<body>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <a href="my_bookings.php" class="btn btn-light rounded-pill mb-4"><i class="bi bi-arrow-left"></i> ย้อนกลับ</a>

                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h3 class="fw-bold mb-4 text-dark"><i class="bi bi-wallet2 text-danger"></i> แจ้งชำระเงิน</h3>

                    <div class="bg-light rounded-4 p-3 mb-4">
                        <div class="small text-muted mb-1">ห้องพักที่จอง:</div>
                        <div class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($room_name); ?></div>
                        <div class="row small text-muted">
                            <div class="col-6">รหัสการจอง: <strong>#BKG-<?php echo str_pad($booking['id'], 4, '0', STR_PAD_LEFT); ?></strong></div>
                            <div class="col-6 text-end">ยอดชำระ: <strong class="text-danger fs-5">฿<?php echo number_format($booking['total_price'], 0); ?></strong></div>
                        </div>
                    </div>

                    <div class="card border-primary bg-primary bg-opacity-10 rounded-4 p-3 mb-4">
                        <h6 class="fw-bold text-primary mb-2"><i class="bi bi-bank"></i> บัญชีสำหรับโอนเงิน</h6>
                        <p class="mb-1 text-dark">ธนาคารไทยพาณิชย์ (SCB)</p>
                        <p class="mb-1 fw-bold text-dark fs-5">123-4-56789-0</p>
                        <small class="text-muted">ชื่อบัญชี: บจก. ซูพรีม บุ๊คกิ้ง แพลตฟอร์ม</small>
                    </div>

                    <?php if ($message != ''): ?>
                        <div class="alert alert-<?php echo $status_msg; ?> text-center rounded-3 mb-3"><?php echo $message; ?></div>
                    <?php endif; ?>

                    <?php if ($booking['status'] == 'pending'): ?>
                        <form action="upload_slip.php?booking_id=<?php echo $booking_id; ?>" method="POST" enctype="multipart/form-data">
                            <div class="upload-box mb-4" onclick="document.getElementById('slipInput').click()">
                                <i class="bi bi-cloud-arrow-up text-muted mb-2" style="font-size: 3.5rem;"></i>
                                <h5 class="fw-bold text-dark">คลิกเพื่ออัปโหลดสลิป</h5>
                                <p class="text-muted small mb-0">รองรับไฟล์ JPG, JPEG, PNG (ไม่เกิน 5MB)</p>
                                <input type="file" name="slip_image" id="slipInput" class="d-none" accept="image/*" required onchange="showPreview(this)">
                            </div>

                            <div id="previewContainer" class="text-center mb-4 d-none">
                                <h6 class="fw-bold text-muted mb-2">รูปภาพที่เลือก:</h6>
                                <img id="slipPreview" src="#" alt="Slip Preview" class="img-fluid rounded-3 border shadow-sm" style="max-height: 300px;">
                            </div>

                            <button type="submit" class="btn btn-danger w-100 py-3 rounded-pill fw-bold fs-5">ยืนยันการส่งสลิป</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-success text-center rounded-4 p-4 mb-0">
                            <i class="bi bi-check-circle fs-1 d-block mb-2"></i>
                            <strong>คุณได้แจ้งชำระเงินเรียบร้อยแล้ว</strong>
                            <p class="small text-muted mb-0 mt-1">สถานะปัจจุบัน: <?php echo $booking['status'] == 'paid' ? 'รอตรวจสอบยอดเงิน' : 'ชำระเงินสำเร็จแล้ว'; ?></p>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <script>
        function showPreview(input) {
            const container = document.getElementById('previewContainer');
            const preview = document.getElementById('slipPreview');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    container.classList.remove('d-none');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>

</html>