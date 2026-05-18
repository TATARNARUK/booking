<?php
session_start();
require_once 'db.php';

// ถ้าล็อกอินอยู่แล้วให้เด้งไปหน้าแรก
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $db = (new Database())->connect();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['role'] = $user['role'];
        
        // แยกทางไปหน้า Admin หรือ หน้าเว็บปกติ
        if ($user['role'] == 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $error = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง';
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - Supreme Booking</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; }
        .split-layout { min-height: 100vh; display: flex; }
        .bg-image-side { 
            background: linear-gradient(rgba(0,0,0,0.2), rgba(0,0,0,0.2)), url('https://picsum.photos/seed/login_bg/1000/1200') center/cover no-repeat;
            flex: 1.2; 
        }
        .form-side { flex: 1; display: flex; align-items: center; justify-content: center; background: #fff; padding: 2rem; }
        .form-container { width: 100%; max-width: 400px; }
        .form-floating > label { color: #6c757d; }
        .form-control:focus { border-color: #ff385c; box-shadow: 0 0 0 0.25rem rgba(255, 56, 92, 0.25); }
        .btn-brand { background-color: #ff385c; border-color: #ff385c; color: white; transition: all 0.3s; }
        .btn-brand:hover { background-color: #e31c5f; border-color: #e31c5f; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(227, 28, 95, 0.3); }
        @media (max-width: 991.98px) { .bg-image-side { display: none; } } /* ซ่อนรูปบนมือถือ */
    </style>
</head>
<body>

    <div class="split-layout">
        <div class="bg-image-side d-none d-lg-block position-relative">
            <div class="position-absolute bottom-0 start-0 p-5 text-white">
                <h2 class="fw-bold fs-1 mb-2">ค้นพบที่พักในฝันของคุณ</h2>
                <p class="fs-5 opacity-75">เริ่มต้นการเดินทางที่แสนพิเศษไปกับ Supreme Booking แพลตฟอร์มที่ได้รับความไว้วางใจจากนักเดินทางทั่วโลก</p>
            </div>
        </div>

        <div class="form-side shadow-lg">
            <div class="form-container">
                <div class="text-center mb-5">
                    <a href="index.php" class="text-decoration-none">
                        <h2 class="fw-bold text-danger mb-0"><i class="bi bi-geo-alt-fill"></i> Supreme Booking</h2>
                    </a>
                    <p class="text-muted mt-2">ยินดีต้อนรับกลับมา! กรุณาเข้าสู่ระบบ</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger rounded-4 d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div><?php echo $error; ?></div>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['registered'])): ?>
                    <div class="alert alert-success rounded-4 d-flex align-items-center" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <div>สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ</div>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control rounded-4" id="email" name="email" placeholder="name@example.com" required>
                        <label for="email"><i class="bi bi-envelope me-1"></i> อีเมล</label>
                    </div>
                    
                    <div class="form-floating mb-4">
                        <input type="password" class="form-control rounded-4" id="password" name="password" placeholder="Password" required>
                        <label for="password"><i class="bi bi-lock me-1"></i> รหัสผ่าน</label>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4 small">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="rememberMe">
                            <label class="form-check-label text-muted" for="rememberMe">จดจำฉันไว้</label>
                        </div>
                        <a href="#" class="text-danger text-decoration-none fw-semibold">ลืมรหัสผ่าน?</a>
                    </div>

                    <button type="submit" class="btn btn-brand w-100 py-3 rounded-pill fw-bold fs-5 mb-4">เข้าสู่ระบบ</button>
                </form>

                <div class="text-center text-muted">
                    ยังไม่มีบัญชีผู้ใช้? <a href="register.php" class="text-danger fw-bold text-decoration-none">สมัครสมาชิกที่นี่</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>