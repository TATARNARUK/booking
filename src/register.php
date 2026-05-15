<?php
session_start();
require_once 'db.php';

$message = '';
$status = '';

// เช็คว่ามีการกดปุ่ม Submit ฟอร์มมาหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->connect();

    // รับค่าจากฟอร์ม (ตัดเรื่องที่อยู่ออกไป)
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password'];
    $phone = htmlspecialchars(trim($_POST['phone']));

    // เช็คว่าอีเมลนี้ซ้ำในระบบหรือไม่
    $check_query = "SELECT id FROM users WHERE email = :email";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':email', $email);
    $check_stmt->execute();

    if ($check_stmt->rowCount() > 0) {
        $status = 'error';
        $message = 'อีเมลนี้ถูกใช้งานแล้ว กรุณาใช้อีเมลอื่น';
    } else {
        // เข้ารหัสรหัสผ่าน (มาตรฐานสากลความปลอดภัย)
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // บันทึกข้อมูลลงฐานข้อมูล (ใส่แค่ข้อมูลที่จำเป็น)
        $insert_query = "INSERT INTO users (first_name, last_name, email, password, phone) 
                         VALUES (:first_name, :last_name, :email, :password, :phone)";
        
        $stmt = $db->prepare($insert_query);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':phone', $phone);

        if ($stmt->execute()) {
            $status = 'success';
            $message = 'สมัครสมาชิกสำเร็จ! คุณสามารถเข้าสู่ระบบได้ทันที';
        } else {
            $status = 'error';
            $message = 'เกิดข้อผิดพลาดในการสมัครสมาชิก โปรดลองอีกครั้ง';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก - Supreme Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Navbar เบื้องต้น -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">🏨 Supreme Booking</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">หน้าแรก</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h4 class="mb-0">สร้างบัญชีผู้ใช้ใหม่</h4>
                    </div>
                    <div class="card-body p-4">
                        
                        <!-- แสดงข้อความแจ้งเตือน -->
                        <?php if($message != ''): ?>
                            <div class="alert alert-<?php echo $status == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form action="register.php" method="POST">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">ชื่อจริง</label>
                                    <input type="text" name="first_name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">นามสกุล</label>
                                    <input type="text" name="last_name" class="form-control" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">อีเมล</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">รหัสผ่าน</label>
                                <input type="password" name="password" class="form-control" minlength="6" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">เบอร์โทรศัพท์</label>
                                <input type="tel" name="phone" class="form-control" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fs-5">สมัครสมาชิก</button>
                            <div class="text-center mt-3">
                                <p>มีบัญชีอยู่แล้ว? <a href="login.php" class="text-decoration-none">เข้าสู่ระบบที่นี่</a></p>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>