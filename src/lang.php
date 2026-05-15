<?php
// src/lang.php
session_start();

// ตรวจสอบการเปลี่ยนภาษาจาก URL (เช่น ?lang=en)
if (isset($_GET['lang']) && in_array($_GET['lang'], ['th', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

// ตั้งค่าภาษาเริ่มต้นเป็นภาษาไทย
$current_lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'th';

// พจนานุกรมคำแปล
$translations = [
    'th' => [
        'site_title' => 'Supreme Booking - จองที่พักระดับโลก',
        'home' => 'หน้าแรก',
        'login' => 'เข้าสู่ระบบ',
        'register' => 'สมัครสมาชิก',
        'my_bookings' => 'ประวัติการจอง',
        'logout' => 'ออกจากระบบ',
        'hero_title' => 'ค้นพบที่พักในฝันของคุณ',
        'hero_subtitle' => 'จองบ้านพัก โรงแรม และสถานที่พักผ่อนสุดพิเศษทั่วโลก',
        'search_location' => 'สถานที่',
        'search_location_ph' => 'คุณอยากไปที่ไหน?',
        'search_checkin' => 'เช็คอิน',
        'search_checkout' => 'เช็คเอาต์',
        'search_guests' => 'ผู้เข้าพัก',
        'search_guests_ph' => 'จำนวนคน',
        'search_btn' => 'ค้นหาที่พัก',
        'popular_rooms' => 'ที่พักยอดนิยมสำหรับคุณ',
        'night' => 'คืน',
        'book_now' => 'ดูรายละเอียด',
        'admin_panel' => '🛠 ระบบหลังบ้าน',
        'welcome' => 'สวัสดี'
    ],
    'en' => [
        'site_title' => 'Supreme Booking - Global Stays',
        'home' => 'Home',
        'login' => 'Sign In',
        'register' => 'Sign Up',
        'my_bookings' => 'My Trips',
        'logout' => 'Log Out',
        'hero_title' => 'Find Your Next Stay',
        'hero_subtitle' => 'Search deals on hotels, homes, and much more...',
        'search_location' => 'Location',
        'search_location_ph' => 'Where are you going?',
        'search_checkin' => 'Check-in',
        'search_checkout' => 'Check-out',
        'search_guests' => 'Guests',
        'search_guests_ph' => 'Add guests',
        'search_btn' => 'Search',
        'popular_rooms' => 'Trending Destinations',
        'night' => 'night',
        'book_now' => 'View Details',
        'admin_panel' => '🛠 Admin Dashboard',
        'welcome' => 'Hello'
    ]
];

// ฟังก์ชันช่วยดึงคำแปลไปใช้ในหน้า HTML
function __($key) {
    global $translations, $current_lang;
    return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
}
?>