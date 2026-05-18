<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = 'รหัสผ่านทั้งสองช่องไม่ตรงกัน';
    } else {
        $db = (new Database())->connect();
        
        // เช็คอีเมลซ้ำ
        $stmt_check = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt_check->bindParam(':email', $email);
        $stmt_check->execute();
        
        if ($stmt_check->fetch()) {
            $error = 'อีเมลนี้ถูกใช้งานแล้ว กรุณาใช้อีเมลอื่น';
        } else {
            // เข้ารหัสผ่านเพื่อความปลอดภัยขั้นสุด
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user'; // ค่าเริ่มต้นคือ user ธรรมดา

            $stmt = $db->prepare("INSERT INTO users (first_name, last_name, email, phone, password, role) VALUES (:first_name, :last_name, :email, :phone, :password, :role)");
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':role', $role);

            if ($stmt->execute()) {
                header("Location: login.php?registered=1");
                exit();
            } else {
                $error = 'เกิดข้อผิดพลาดในการสมัครสมาชิก โปรดลองอีกครั้ง';
            }
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; }
        .split-layout { min-height: 100vh; display: flex; }
        .bg-image-side { 
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('https://picsum.photos/seed/register_bg/1000/1200') center/cover no-repeat;
            flex: 1; 
        }
        .form-side { flex: 1.5; display: flex; align-items: center; justify-content: center; background: #fff; padding: 2rem; overflow-y: auto; }
        .form-container { width: 100%; max-width: 500px; margin: auto; padding-top: 2rem; padding-bottom: 2rem;}
        .form-floating > label { color: #6c757d; }
        .form-control:focus { border-color: #ff385c; box-shadow: 0 0 0 0.25rem rgba(255, 56, 92, 0.25); }
        .btn-brand { background-color: #ff385c; border-color: #ff385c; color: white; transition: all 0.3s; }
        .btn-brand:hover { background-color: #e31c5f; border-color: #e31c5f; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(227, 28, 95, 0.3); }
        @media (max-width: 991.98px) { .bg-image-side { display: none; } }
    </style>
</head>
<body>

    <div class="split-layout">
        <div class="bg-image-side d-none d-lg-block position-relative order-lg-2">
            <div class="position-absolute bottom-0 end-0 p-5 text-white text-end">
                <h2 class="fw-bold fs-1 mb-2">เป็นส่วนหนึ่งกับเรา</h2>
                <p class="fs-5 opacity-75">สมัครสมาชิกวันนี้ เพื่อรับข้อเสนอสุดพิเศษและจัดการประวัติการจองได้ง่ายกว่าที่เคย</p>
            </div>
        </div>

        <div class="form-side shadow-lg order-lg-1">
            <div class="form-container">
                <div class="mb-4">
                    <a href="index.php" class="text-decoration-none">
                        <h2 class="fw-bold text-danger mb-0"><i class="bi bi-geo-alt-fill"></i> Supreme Booking</h2>
                    </a>
                    <h3 class="fw-bold mt-4">สร้างบัญชีผู้ใช้ใหม่</h3>
                    <p class="text-muted">กรอกข้อมูลด้านล่างเพื่อเริ่มการเดินทางของคุณ</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger rounded-4 d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div><?php echo $error; ?></div>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST">
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control rounded-4" id="first_name" name="first_name" placeholder="ชื่อ" required>
                                <label for="first_name">ชื่อจริง</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control rounded-4" id="last_name" name="last_name" placeholder="นามสกุล" required>
                                <label for="last_name">นามสกุล</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="email" class="form-control rounded-4" id="email" name="email" placeholder="name@example.com" required>
                        <label for="email"><i class="bi bi-envelope me-1"></i> อีเมล</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="tel" class="form-control rounded-4" id="phone" name="phone" placeholder="08X-XXX-XXXX" required>
                        <label for="phone"><i class="bi bi-telephone me-1"></i> เบอร์โทรศัพท์</label>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control rounded-4" id="password" name="password" placeholder="Password" required minlength="6">
                        <label for="password"><i class="bi bi-lock me-1"></i> รหัสผ่าน (ขั้นต่ำ 6 ตัวอักษร)</label>
                    </div>

                    <div class="form-floating mb-4">
                        <input type="password" class="form-control rounded-4" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required minlength="6">
                        <label for="confirm_password"><i class="bi bi-shield-lock me-1"></i> ยืนยันรหัสผ่าน</label>
                    </div>

                    <button type="submit" class="btn btn-brand w-100 py-3 rounded-pill fw-bold fs-5 mb-4">สมัครสมาชิกเลย</button>
                </form>

                <div class="text-center text-muted">
                    มีบัญชีอยู่แล้วใช่ไหม? <a href="login.php" class="text-danger fw-bold text-decoration-none">เข้าสู่ระบบ</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>