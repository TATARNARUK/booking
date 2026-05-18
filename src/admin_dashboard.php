<?php
session_start();
require_once 'db.php';

// 🛡️ เช็คสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->connect();

// 1. ดึงข้อมูลสรุปยอด (เฉพาะที่โอนแล้ว/ยืนยันแล้ว)
$stmt_rev = $db->query("SELECT SUM(total_price) as total_rev FROM bookings WHERE status IN ('paid', 'confirmed')");
$total_revenue = $stmt_rev->fetch(PDO::FETCH_ASSOC)['total_rev'] ?? 0;

$stmt_book = $db->query("SELECT COUNT(*) as total_book FROM bookings");
$total_bookings = $stmt_book->fetch(PDO::FETCH_ASSOC)['total_book'] ?? 0;

$stmt_room = $db->query("SELECT COUNT(*) as total_room FROM rooms");
$total_rooms = $stmt_room->fetch(PDO::FETCH_ASSOC)['total_room'] ?? 0;

// 2. ดึงข้อมูลสำหรับทำกราฟ
$query_chart = "SELECT r.name_th, SUM(b.total_price) as room_rev 
                FROM bookings b 
                JOIN rooms r ON b.room_id = r.id 
                WHERE b.status IN ('paid', 'confirmed')
                GROUP BY r.id";
$stmt_chart = $db->query($query_chart);
$chart_results = $stmt_chart->fetchAll(PDO::FETCH_ASSOC);

$chart_labels = [];
$chart_data = [];
foreach ($chart_results as $row) {
    $chart_labels[] = $row['name_th'];
    $chart_data[] = $row['room_rev'];
}

// 3. ดึงข้อมูลการจองล่าสุด
$query_table = "SELECT b.*, r.name_th AS room_name 
                FROM bookings b 
                LEFT JOIN rooms r ON b.room_id = r.id 
                ORDER BY b.id DESC LIMIT 20";
$stmt_table = $db->query($query_table);
$bookings = $stmt_table->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ภาพรวมระบบรายงาน - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Prompt:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Inter', 'Prompt', sans-serif; background-color: #f4f6f9; }
        .stat-card { border: none; border-radius: 15px; transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .icon-box { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px; }
        .table-card { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
        
        /* =========================================
           PRINT MODE (ตั้งค่าสำหรับการสั่งพิมพ์ PDF/A4) 
           ========================================= */
        @media print {
            .navbar, button[onclick="window.print()"], .action-col, .action-btn { display: none !important; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            body { background-color: #ffffff !important; }
            .row { display: flex !important; flex-wrap: nowrap !important; }
            .col-md-4 { width: 33.33% !important; padding: 0 10px !important; }
            .table-responsive { overflow-x: visible !important; }
            .table { font-size: 11px !important; table-layout: fixed !important; width: 100% !important; }
            .table td, .table th { white-space: normal !important; word-wrap: break-word !important; }
            canvas { max-width: 100% !important; height: auto !important; }
            .card { box-shadow: none !important; border: 1px solid #ddd !important; }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="admin_dashboard.php">
                <i class="bi bi-speedometer2 text-danger"></i> Admin Control Panel
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link active" href="admin_dashboard.php">ระบบรายงาน & การจอง</a>
                <a class="nav-link" href="admin_rooms.php">จัดการห้องพัก</a>
                <a class="nav-link text-light" href="index.php"><i class="bi bi-box-arrow-up-right"></i> ดูหน้าเว็บ</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 my-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">📊 ภาพรวมธุรกิจ (Dashboard)</h2>
            <button onclick="window.print()" class="btn btn-outline-secondary rounded-pill">
                <i class="bi bi-printer"></i> พิมพ์รายงาน
            </button>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
            <div class="alert alert-success rounded-pill text-center alert-dismissible fade show">
                <i class="bi bi-check-circle-fill"></i> อัปเดตสถานะการจองเรียบร้อยแล้ว!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card stat-card bg-white shadow-sm h-100 p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-success bg-opacity-10 text-success me-3"><i class="bi bi-cash-coin"></i></div>
                        <div>
                            <p class="text-muted mb-1 fs-6">รายได้รวม (ยืนยันแล้ว)</p>
                            <h3 class="fw-bold mb-0 text-dark">฿<?php echo number_format($total_revenue, 0); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-white shadow-sm h-100 p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary me-3"><i class="bi bi-calendar-check"></i></div>
                        <div>
                            <p class="text-muted mb-1 fs-6">จำนวนการจอง (ทั้งหมด)</p>
                            <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($total_bookings); ?> <span class="fs-6 fw-normal text-muted">รายการ</span></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-white shadow-sm h-100 p-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-danger bg-opacity-10 text-danger me-3"><i class="bi bi-door-open"></i></div>
                        <div>
                            <p class="text-muted mb-1 fs-6">ห้องพักในระบบ</p>
                            <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($total_rooms); ?> <span class="fs-6 fw-normal text-muted">ห้อง</span></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-lg-12">
                <div class="card table-card h-100 p-4">
                    <h5 class="fw-bold mb-4">📈 สถิติรายได้แยกตามห้องพัก</h5>
                    <canvas id="revenueChart" height="60"></canvas>
                </div>
            </div>
        </div>

        <h4 class="fw-bold mb-3">📋 รายการจองล่าสุด</h4>
        <div class="card table-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-3">รหัสการจอง</th>
                                <th>ลูกค้า / เบอร์ติดต่อ</th>
                                <th>ห้องพัก</th>
                                <th>เช็คอิน - เช็คเอาต์</th>
                                <th>ยอดชำระ</th>
                                <th class="text-center">สถานะ</th>
                                <th class="text-center pe-4 action-col">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($bookings) > 0): ?>
                                <?php foreach($bookings as $b): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-secondary">#BKG-<?php echo str_pad($b['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($b['customer_name']); ?></div>
                                        <div class="small text-muted"><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($b['customer_phone']); ?></div>
                                    </td>
                                    <td><span class="badge bg-dark bg-opacity-10 text-dark border"><i class="bi bi-house"></i> <?php echo htmlspecialchars($b['room_name']); ?></span></td>
                                    <td>
                                        <div class="small text-dark fw-semibold"><?php echo date('d/m/Y', strtotime($b['check_in'])); ?></div>
                                        <div class="small text-muted">ถึง <?php echo date('d/m/Y', strtotime($b['check_out'])); ?></div>
                                    </td>
                                    <td class="fw-bold text-success">฿<?php echo number_format($b['total_price'], 0); ?></td>
                                    
                                    <td class="text-center">
                                        <?php 
                                            if ($b['status'] == 'pending') { echo '<span class="badge bg-warning text-dark rounded-pill px-3 py-2">รอชำระเงิน</span>'; }
                                            elseif ($b['status'] == 'paid') { echo '<span class="badge bg-info text-dark rounded-pill px-3 py-2">รอตรวจสอบ</span>'; }
                                            elseif ($b['status'] == 'confirmed') { echo '<span class="badge bg-success rounded-pill px-3 py-2">ยืนยันแล้ว</span>'; }
                                            else { echo '<span class="badge bg-danger rounded-pill px-3 py-2">ยกเลิก</span>'; }
                                        ?>
                                    </td>

                                    <td class="text-center pe-4 action-col">
                                        <div class="d-flex justify-content-center gap-1">
                                            <?php if (!empty($b['payment_slip'])): ?>
                                                <a href="uploads/slips/<?php echo htmlspecialchars($b['payment_slip']); ?>" target="_blank" class="btn btn-sm btn-outline-primary action-btn" title="ดูสลิปโอนเงิน"><i class="bi bi-receipt"></i></a>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-outline-secondary action-btn" disabled title="ยังไม่ส่งสลิป"><i class="bi bi-receipt"></i></button>
                                            <?php endif; ?>

                                            <?php if ($b['status'] == 'paid' || $b['status'] == 'pending'): ?>
                                                <a href="update_status.php?id=<?php echo $b['id']; ?>&status=confirmed" class="btn btn-sm btn-success action-btn" onclick="return confirm('ยืนยันอนุมัติการจองนี้ใช่หรือไม่?');" title="อนุมัติ"><i class="bi bi-check-lg"></i></a>
                                                <a href="update_status.php?id=<?php echo $b['id']; ?>&status=cancelled" class="btn btn-sm btn-danger action-btn" onclick="return confirm('ต้องการยกเลิกการจองนี้ใช่หรือไม่?');" title="ยกเลิก"><i class="bi bi-x-lg"></i></a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted">ยังไม่มีรายการจองในระบบ</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // โค้ดกราฟ Chart.js เหมือนเดิมครับ
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'รายได้รวม (บาท)',
                    data: <?php echo json_encode($chart_data); ?>,
                    backgroundColor: 'rgba(255, 56, 92, 0.8)',
                    borderColor: 'rgba(255, 56, 92, 1)',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>