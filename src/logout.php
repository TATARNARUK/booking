<?php
// src/logout.php
session_start();
session_destroy(); // ทำลาย Session ทั้งหมด
header("Location: index.php"); // เด้งกลับไปหน้าแรก
exit();
?>