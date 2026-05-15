<?php
session_start();
require_once 'db.php';

// ถ้าล็อกอินอยู่แล้ว ให้เด้งกลับไปหน้าแรกเลย
if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->connect();

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // ค้นหาข้อมูลผู้ใช้จากอีเมล
    $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // ตรวจสอบรหัสผ่านว่าตรงกับที่เข้ารหัสไว้หรือไม่
        if (password_verify($password, $user['password'])) {
            // สร้าง Session จดจำผู้ใช้
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['role'] = $user['role']; // เผื่อเอาไว้แยก Admin กับ User
            
            // ล็อกอินสำเร็จ เด้งไปหน้าแรก
            header("Location: index.php");
            exit();
        } else {
            $error_message = 'รหัสผ่านไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง';
        }
    } else {
        $error_message = 'ไม่พบอีเมลนี้ในระบบ กรุณาสมัครสมาชิก';
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - Supreme Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Navbar -->
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
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white text-center py-3">
                        <h4 class="mb-0">เข้าสู่ระบบ</h4>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php if($error_message != ''): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>

                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">อีเมล</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">รหัสผ่าน</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-dark w-100 py-2 fs-5">เข้าสู่ระบบ</button>
                            <div class="text-center mt-3">
                                <p>ยังไม่มีบัญชี? <a href="register.php" class="text-decoration-none">สมัครสมาชิกที่นี่</a></p>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>