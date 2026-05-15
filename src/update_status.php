<?php
session_start();
require_once 'db.php';

// ตรวจสอบความปลอดภัย
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit('Unauthorized');
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $database = new Database();
    $db = $database->connect();
    
    $booking_id = $_GET['id'];
    $new_status = $_GET['status']; // 'confirmed' หรือ 'cancelled'

    $query = "UPDATE bookings SET status = :status WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $new_status);
    $stmt->bindParam(':id', $booking_id);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php");
        exit();
    }
}
?>