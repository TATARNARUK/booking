<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->connect();

    $name_th = $_POST['name_th'];
    $name_en = $_POST['name_en'];
    $description_th = $_POST['description_th'];
    $description_en = $_POST['description_en'];
    $price = $_POST['price'];
    $capacity = $_POST['capacity'];
    
    // ระบบจัดการไฟล์ภาพ
    $target_dir = "uploads/";
    $file_name = time() . "_" . basename($_FILES["image"]["name"]); 
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image_url = "uploads/" . $file_name;
        
        $query = "INSERT INTO rooms (name_th, name_en, description_th, description_en, price, capacity, image_url) 
                  VALUES (:name_th, :name_en, :description_th, :description_en, :price, :capacity, :image_url)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name_th', $name_th);
        $stmt->bindParam(':name_en', $name_en);
        $stmt->bindParam(':description_th', $description_th);
        $stmt->bindParam(':description_en', $description_en);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':capacity', $capacity);
        $stmt->bindParam(':image_url', $image_url);

        if ($stmt->execute()) {
            header("Location: admin_rooms.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มห้องพัก 2 ภาษา - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h3 class="fw-bold mb-4">🆕 เพิ่มห้องพักใหม่ (รองรับ 2 ภาษา)</h3>
                        <form action="admin_room_add.php" method="POST" enctype="multipart/form-data">
                            
                            <div class="row bg-light p-3 rounded mb-3">
                                <h5 class="text-primary fw-bold">🇹🇭 ข้อมูลภาษาไทย</h5>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">ชื่อห้องพัก</label>
                                    <input type="text" name="name_th" class="form-control" required placeholder="เช่น ห้องสวีทวิวทะเล">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">รายละเอียด</label>
                                    <textarea name="description_th" class="form-control" rows="2" required></textarea>
                                </div>
                            </div>

                            <div class="row bg-light p-3 rounded mb-4">
                                <h5 class="text-danger fw-bold">🇬🇧 English Information</h5>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Room Name</label>
                                    <input type="text" name="name_en" class="form-control" required placeholder="e.g. Ocean View Suite">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Description</label>
                                    <textarea name="description_en" class="form-control" rows="2" required></textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">ราคาต่อคืน (บาท)</label>
                                    <input type="number" name="price" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">จำนวนผู้พักสูงสุด</label>
                                    <input type="number" name="capacity" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">รูปภาพห้องพัก</label>
                                <input type="file" name="image" class="form-control" accept="image/*" required>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-dark w-100 py-2 fw-bold">บันทึกข้อมูลห้องพัก</button>
                                <a href="admin_rooms.php" class="btn btn-outline-secondary w-100 py-2">ยกเลิก</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>