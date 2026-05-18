<?php
require_once 'db.php';

$database = new Database();
$db = $database->connect();

echo "กำลังสร้างข้อมูลห้องพัก 100 แห่ง...<br>";

// คำศัพท์สำหรับสุ่มสร้างชื่อห้องแบบเนียนๆ
$th_adjectives = ['ลักชัวรี่', 'โมเดิร์น', 'มินิมอล', 'ริมทะเล', 'วิวภูเขา', 'ใจกลางเมือง', 'สุดหรู', 'สไตล์ลอฟท์', 'ส่วนตัว', 'วิวพาโนรามา'];
$en_adjectives = ['Luxury', 'Modern', 'Minimalist', 'Beachfront', 'Mountain View', 'Downtown', 'Premium', 'Loft Style', 'Private', 'Panoramic'];

$th_types = ['พูลวิลล่า', 'ห้องสวีท', 'ดีลักซ์', 'เต็นท์โดม', 'เพนท์เฮาส์', 'ห้องพัก', 'แฟมิลี่รูม'];
$en_types = ['Pool Villa', 'Suite', 'Deluxe Room', 'Glamping Dome', 'Penthouse', 'Room', 'Family Room'];

$th_locations = ['ภูเก็ต', 'เชียงใหม่', 'พัทยา', 'หัวหิน', 'กระบี่', 'สมุย', 'กรุงเทพ', 'เขาใหญ่'];
$en_locations = ['Phuket', 'Chiang Mai', 'Pattaya', 'Hua Hin', 'Krabi', 'Samui', 'Bangkok', 'Khao Yai'];

$success_count = 0;

for ($i = 1; $i <= 100; $i++) {
    // สุ่มคำมาประกอบร่างกัน
    $rand_adj = rand(0, 9);
    $rand_type = rand(0, 6);
    $rand_loc = rand(0, 7);

    // สร้างชื่อ 2 ภาษา
    $name_th = $th_adjectives[$rand_adj] . $th_types[$rand_type] . ' @' . $th_locations[$rand_loc];
    $name_en = $en_adjectives[$rand_adj] . ' ' . $en_types[$rand_type] . ' in ' . $en_locations[$rand_loc];

    // สร้างคำอธิบาย 2 ภาษา
    $description_th = "สัมผัสประสบการณ์การพักผ่อนระดับพรีเมียมกับ {$name_th} พร้อมสิ่งอำนวยความสะดวกครบครัน สระว่ายน้ำ และบริการระดับ 5 ดาว";
    $description_en = "Experience premium relaxation at {$name_en} with full amenities, swimming pool, and 5-star service.";

    // สุ่มราคา 1,500 - 15,000 บาท (ปัดเศษหลักร้อยให้สวยๆ)
    $price = floor(rand(1500, 15000) / 100) * 100;
    
    // สุ่มความจุผู้เข้าพัก 2 - 8 คน
    $capacity = rand(2, 8);

    // 🌟 ดึงรูปภาพแบบสุ่มจากระบบ API ของ Picsum (ได้รูปสวยๆ ไม่ซ้ำกัน 100 รูปแน่นอน)
    // ใช้ seed เพื่อให้แต่ละห้องได้รูปคงที่ ไม่เปลี่ยนไปมาเวลาโหลดหน้าเว็บใหม่
    $image_url = "https://picsum.photos/seed/supreme_room_{$i}/800/600";

    // บันทึกลง Database
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
        $success_count++;
    }
}

echo "<h2 style='color: green;'>🎉 สร้างข้อมูลสำเร็จแล้วจำนวน {$success_count} ห้อง!</h2>";
echo "<a href='index.php'>กลับไปดูที่หน้าแรก</a>";
?>