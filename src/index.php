<?php
require_once 'lang.php'; // โหลดระบบภาษาก่อนเสมอ
/** @var string $current_lang */ // <--- เพิ่มบรรทัดนี้เข้าไปบอก VS Code ว่าตัวแปรนี้มีอยู่จริง เพื่อให้การเขียนโค้ดต่อไปนี้มีความสะดวกมากขึ้น
require_once 'db.php';

$database = new Database();
$db = $database->connect();

$query = "SELECT * FROM rooms";
$stmt = $db->prepare($query);
$stmt->execute();
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('site_title'); ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        body {
            font-family: 'Inter', 'Prompt', sans-serif;
            background-color: #f7f9fa;
        }

        /* Navbar ยุคใหม่ คลีนใส */
        .navbar-custom {
            background-color: #ffffff !important;
            box-shadow: 0 1px 15px rgba(0, 0, 0, 0.05);
            padding: 15px 0;
        }

        .navbar-custom .nav-link {
            color: #222222 !important;
            font-weight: 600;
        }

        /* Hero Section สไตล์ Airbnb */
        .hero-section {
            background: linear-gradient(to right, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.2)), url('https://images.unsplash.com/photo-1542314831-c6a4d14d8373?q=80&w=2000&auto=format&fit=crop') center/cover;
            height: 60vh;
            min-height: 500px;
            display: flex;
            align-items: center;
            border-radius: 0 0 40px 40px;
            margin-bottom: -50px;
        }

        /* Search Box ลอยตัว */
        .search-container {
            background: #ffffff;
            border-radius: 50px;
            padding: 10px 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            transform: translateY(50%);
            position: relative;
            z-index: 10;
        }

        .search-input-group {
            border-right: 1px solid #ebebeb;
            padding: 5px 20px;
        }

        .search-input-group:last-child {
            border-right: none;
        }

        .search-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 2px;
        }

        .search-control {
            border: none;
            padding: 0;
            font-size: 0.95rem;
            background: transparent;
            box-shadow: none !important;
        }

        .btn-search {
            background-color: #ff385c;
            /* สีโทนดึงดูดใจ */
            border-radius: 50px;
            padding: 12px 30px;
            color: white;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-search:hover {
            background-color: #e31c5f;
            color: white;
        }

        /* Card ดีไซน์สากล */
        .room-card {
            border: none;
            border-radius: 16px;
            transition: all 0.3s ease;
            cursor: pointer;
            background: transparent;
        }

        .room-card:hover {
            transform: translateY(-5px);
        }

        .room-card img {
            height: 280px;
            object-fit: cover;
            border-radius: 16px;
            margin-bottom: 12px;
        }

        .room-card .card-body {
            padding: 5px 0;
        }

        /* เพิ่มเอฟเฟกต์สำหรับ Landing Page */
        .transition-hover {
            transition: all 0.3s ease;
        }

        .transition-hover:hover {
            transform: translateY(-10px);
        }

        .hover-white:hover {
            color: #ffffff !important;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-danger fs-4" href="index.php">
                <i class="bi bi-geo-alt-fill"></i> Supreme Booking
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">

                    <li class="nav-item me-3">
                        <div class="dropdown">
                            <button class="btn btn-light rounded-pill dropdown-toggle border" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-globe2"></i> <?php echo strtoupper($current_lang); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                <li><a class="dropdown-item" href="?lang=th">🇹🇭 ภาษาไทย</a></li>
                                <li><a class="dropdown-item" href="?lang=en">🇬🇧 English</a></li>
                            </ul>
                        </div>
                    </li>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle bg-light rounded-pill px-3 py-2 border" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle fs-5 text-secondary align-middle me-1"></i>
                                <?php echo __('welcome') . ', ' . htmlspecialchars($_SESSION['first_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 rounded-4">
                                <?php if ($_SESSION['role'] == 'admin'): ?>
                                    <li><a class="dropdown-item fw-bold" href="admin_dashboard.php"><?php echo __('admin_panel'); ?></a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="my_bookings.php"><?php echo __('my_bookings'); ?></a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><?php echo __('logout'); ?></a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php"><?php echo __('login'); ?></a></li>
                        <li class="nav-item ms-2"><a class="btn btn-dark rounded-pill px-4" href="register.php"><?php echo __('register'); ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <header class="position-relative">
        <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">

            <div class="carousel-indicators" style="margin-bottom: 80px;">
                <?php foreach ($rooms as $index => $room): ?>
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?php echo $index; ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>" aria-current="true"></button>
                <?php endforeach; ?>
            </div>

            <div class="carousel-inner" style="border-radius: 0 0 40px 40px; overflow: hidden; height: 60vh; min-height: 500px;">
                <?php foreach ($rooms as $index => $room): ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?> h-100" data-bs-interval="3000">
                        <div style="background: linear-gradient(to right, rgba(0,0,0,0.7), rgba(0,0,0,0.3)), url('<?php echo htmlspecialchars($room['image_url']); ?>') center/cover; height: 100%; width: 100%;"></div>

                        <div class="carousel-caption d-flex flex-column justify-content-center h-100 text-start" style="bottom: 0; padding-bottom: 50px;">
                            <div class="container">
                                <h1 class="display-3 fw-bold mb-3 text-white"><?php echo __('hero_title'); ?></h1>
                                <h3 class="fs-4 fw-bold text-white mb-2">
                                    <?php
                                    // ดึงข้อมูลตามภาษาปัจจุบัน (ถ้าไม่มีภาษาอังกฤษ ให้ดึงไทยมาแสดงแทน)
                                    $name_key = 'name_' . $current_lang;
                                    echo htmlspecialchars(!empty($room[$name_key]) ? $room[$name_key] : $room['name_th']);
                                    ?>
                                    <p class="fs-5 fw-light text-light d-none d-md-block" style="max-width: 600px;">
                                        <?php
                                        $desc_key = 'description_' . $current_lang;
                                        echo htmlspecialchars(!empty($room[$desc_key]) ? $room[$desc_key] : $room['description_th']);
                                        ?></p>
                                    <a href="book_room.php?room_id=<?php echo $room['id']; ?>" class="btn btn-outline-light rounded-pill mt-3 px-4 py-2">
                                        <?php echo __('book_now'); ?>
                                    </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" style="width: 5%;">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" style="width: 5%;">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </header>

    <div class="container position-relative">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="search-container d-none d-md-flex align-items-center">
                    <div class="search-input-group flex-grow-1">
                        <div class="search-label"><?php echo __('search_location'); ?></div>
                        <input type="text" class="search-control form-control" placeholder="<?php echo __('search_location_ph'); ?>">
                    </div>
                    <div class="search-input-group flex-grow-1">
                        <div class="search-label"><?php echo __('search_checkin'); ?></div>
                        <input type="text" class="search-control form-control text-muted" placeholder="<?php echo date('d M Y'); ?>" onfocus="(this.type='date')" onblur="(this.type='text')">
                    </div>
                    <div class="search-input-group flex-grow-1">
                        <div class="search-label"><?php echo __('search_checkout'); ?></div>
                        <input type="text" class="search-control form-control text-muted" placeholder="<?php echo date('d M Y', strtotime('+1 day')); ?>" onfocus="(this.type='date')" onblur="(this.type='text')">
                    </div>
                    <div class="search-input-group flex-grow-1">
                        <div class="search-label"><?php echo __('search_guests'); ?></div>
                        <input type="number" class="search-control form-control" placeholder="<?php echo __('search_guests_ph'); ?>" min="1">
                    </div>
                    <div class="ps-3 pe-1">
                        <button class="btn btn-search border-0"><i class="bi bi-search me-1"></i> <?php echo __('search_btn'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container" style="margin-top: 100px; margin-bottom: 100px;">
        <h3 class="fw-bold mb-4 text-dark"><?php echo __('popular_rooms'); ?></h3>
        <div class="row g-4">
            <?php foreach ($rooms as $room): ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card room-card" onclick="window.location.href='book_room.php?room_id=<?php echo $room['id']; ?>'">
                        <img src="<?php echo htmlspecialchars($room['image_url']); ?>" alt="Room Image">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="fw-bold mb-1 text-dark">
                                    <?php
                                    // ดึงข้อมูลตามภาษาปัจจุบัน (ถ้าไม่มีภาษาอังกฤษ ให้ดึงไทยมาแสดงแทน)
                                    $name_key = 'name_' . $current_lang;
                                    echo htmlspecialchars(!empty($room[$name_key]) ? $room[$name_key] : $room['name_th']);
                                    ?></h5>
                                <span class="badge bg-light text-dark border"><i class="bi bi-star-fill text-warning"></i> 4.9</span>
                            </div>
                            <p class="text-muted small mb-2 text-truncate">
                                <?php
                                $desc_key = 'description_' . $current_lang;
                                echo htmlspecialchars(!empty($room[$desc_key]) ? $room[$desc_key] : $room['description_th']);
                                ?>
                            </p>
                            <div class="mt-2">
                                <span class="fw-bold text-dark fs-5">฿<?php echo number_format($room['price'], 0); ?></span>
                                <span class="text-muted">/ <?php echo __('night'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="container my-5 py-5 border-top">
        <div class="row text-center g-4">
            <div class="col-md-4">
                <div class="p-4 transition-hover">
                    <i class="bi bi-shield-check text-danger" style="font-size: 3.5rem;"></i>
                    <h4 class="fw-bold mt-4">ปลอดภัย 100%</h4>
                    <p class="text-muted mt-2">ระบบการจองและเก็บข้อมูลที่ได้รับมาตรฐานสากล มั่นใจได้ในทุกการทำธุรกรรม</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 transition-hover">
                    <i class="bi bi-headset text-danger" style="font-size: 3.5rem;"></i>
                    <h4 class="fw-bold mt-4">บริการ 24 ชั่วโมง</h4>
                    <p class="text-muted mt-2">ทีมงานซัพพอร์ตมืออาชีพ พร้อมดูแลและช่วยเหลือคุณตลอดการเข้าพัก</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 transition-hover">
                    <i class="bi bi-tags text-danger" style="font-size: 3.5rem;"></i>
                    <h4 class="fw-bold mt-4">ราคาดีที่สุด</h4>
                    <p class="text-muted mt-2">รับประกันราคาห้องพักที่คุ้มค่าที่สุด ไม่มีบวกเพิ่มหรือค่าธรรมเนียมแอบแฝง</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-danger text-white py-5 text-center mt-5" style="background: linear-gradient(135deg, #ff385c 0%, #e31c5f 100%);">
        <div class="container py-5">
            <h2 class="display-5 fw-bold mb-3">พร้อมที่จะเริ่มการพักผ่อนของคุณหรือยัง?</h2>
            <p class="fs-5 mb-4 text-white-80">สมัครสมาชิกวันนี้ เพื่อรับข้อเสนอสุดพิเศษและจัดการประวัติการจองได้ง่ายกว่าที่เคย</p>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="register.php" class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-bold text-danger shadow-sm">สมัครสมาชิกฟรีทันที</a>
            <?php else: ?>
                <a href="#heroCarousel" class="btn btn-dark btn-lg rounded-pill px-5 py-3 fw-bold shadow-sm">ค้นหาที่พักเลย</a>
            <?php endif; ?>
        </div>
    </div>

    <footer class="bg-dark text-light py-5">
        <div class="container mt-4">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <h4 class="fw-bold mb-4"><i class="bi bi-geo-alt-fill text-danger"></i> Supreme Booking</h4>
                    <p class="text-white pe-lg-4">แพลตฟอร์มจองที่พักระดับสากล มอบประสบการณ์การพักผ่อนที่ดีที่สุดให้คุณในทุกการเดินทาง ไม่ว่าจะเป็นทริปธุรกิจหรือวันหยุดพักผ่อน</p>
                </div>

                <div class="col-lg-2 col-md-6 offset-lg-2">
                    <h5 class="fw-bold mb-4 text-white">เมนูหลัก</h5>
                    <ul class="list-unstyled text-white">
                        <li class="mb-2"><a href="index.php" class="text-white">หน้าแรก</a></li>
                        <li class="mb-2"><a href="#" class="text-white">เกี่ยวกับเรา</a></li>
                        <li class="mb-2"><a href="#" class="text-white">เงื่อนไขการจอง</a></li>
                        <li class="mb-2"><a href="#" class="text-white">ติดต่อเรา</a></li>
                    </ul>
                </div>

                <div class="col-lg-4 col-md-12">
                    <h5 class="fw-bold mb-4 text-white">ติดตามเรา</h5>
                    <p class="text-muted mb-3">รับข่าวสารและโปรโมชั่นใหม่ๆ ก่อนใคร</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="btn btn-outline-light rounded-circle" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="btn btn-outline-light rounded-circle" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="btn btn-outline-light rounded-circle" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;"><i class="bi bi-twitter-x"></i></a>
                    </div>
                </div>
            </div>

            <hr class="mt-5 mb-4 border-secondary">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center text-muted small">
                <div class="mb-2 mb-md-0 text-white">
                    &copy; <?php echo date('Y'); ?> Supreme Booking. All rights reserved.
                </div>
                <div class="d-flex gap-3">
                    <a href="#" class="text-white">Privacy Policy</a>
                    <a href="#" class="text-white">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>