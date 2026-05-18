<?php
session_start();
require_once 'db.php';

// ป้องกันคนที่ไม่ใช่ Admin แอบเข้ามาใช้ไฟล์นี้
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $db = (new Database())->connect();
    $id = $_GET['id'];
    $status = $_GET['status']; // รับค่า confirmed หรือ cancelled

    // อัปเดตสถานะในตาราง bookings
    $stmt = $db->prepare("UPDATE bookings SET status = :status WHERE id = :id");
    
    if ($stmt->execute([':status' => $status, ':id' => $id])) {
        // อัปเดตเสร็จให้เด้งกลับไปหน้า Dashboard อัตโนมัติ
        header("Location: admin_dashboard.php?msg=updated");
        exit();
    } else {
        echo "เกิดข้อผิดพลาดในการอัปเดตสถานะ";
    }
} else {
    header("Location: admin_dashboard.php");
    exit();
}
?>