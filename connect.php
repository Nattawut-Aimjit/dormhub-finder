<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$host = "localhost";
$user = "root";
$pass = "";
$db   = "project";

$con = mysqli_connect($host, $user, $pass, $db);

if (!$con) {
    die("เชื่อมต่อฐานข้อมูลไม่สำเร็จ: " . mysqli_connect_error());
}

mysqli_set_charset($con, "utf8");

// ฟังก์ชันคำนวณระยะทาง (Haversine formula) หน่วย: กิโลเมตร
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $R = 6371; // รัศมีของโลกในกิโลเมตร
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $R * $c;
}

// ฟังก์ชันแปลง type ID เป็นชื่อประเภท
function getTypeName($typeId) {
    $types = [
        1 => 'หอพัก',
        2 => 'อพาร์ทเม้นท์',
        3 => 'คอนโด',
        4 => 'บ้านพัก'
    ];
    return $types[$typeId] ?? 'อื่น ๆ';
}

// ประมวลผลคำขอ AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    if ($_POST['action'] === 'login') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบ']);
            exit;
        }
        
        // ค้นหา user
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'username', 'message' => 'ไม่พบชื่อผู้ใช้นี้']);
            exit;
        }
        
        // ตรวจสอบรหัสผ่าน
        if (!password_verify($password, $user['password'])) {
            echo json_encode(['success' => false, 'error' => 'password', 'message' => 'รหัสผ่านไม่ถูกต้อง']);
            exit;
        }
        
        // สร้าง session
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'photo' => $user['photo'] ?? 'คน.png',
            'role' => $user['role'] 
        ];
        
        echo json_encode([
        'success' => true,
        'message' => 'เข้าสู่ระบบสำเร็จ',
        'role' => $user['role']
        ]);

        exit;
    }
    
    elseif (isset($_POST['action']) && $_POST['action'] === 'register') {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบ']);
            exit;
        }
        // ensure phone column exists
        $colCheck = mysqli_query($con, "SHOW COLUMNS FROM users LIKE 'phone'");
        if ($colCheck && mysqli_num_rows($colCheck) === 0) {
            mysqli_query($con, "ALTER TABLE users ADD phone VARCHAR(50) NULL");
        }
        
        // ตรวจสอบชื่อผู้ใช้ซ้ำ
        $check = mysqli_prepare($con, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($check, "s", $username);
        mysqli_stmt_execute($check);
        if (mysqli_stmt_get_result($check)->num_rows > 0) {
            echo json_encode(['success' => false, 'error' => 'username', 'message' => 'ชื่อผู้ใช้นี้ถูกใช้แล้ว']);
            exit;
        }
        
        // ตรวจสอบ email ซ้ำ
        $check = mysqli_prepare($con, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        if (mysqli_stmt_get_result($check)->num_rows > 0) {
            echo json_encode(['success' => false, 'error' => 'email', 'message' => 'อีเมลนี้ถูกใช้แล้ว']);
            exit;
        }
        
        // แฮช รหัสผ่าน
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // บันทึกลง DB (รวม phone ถ้ามี)
        $sql = "INSERT INTO users (username, email, password, photo, phone) VALUES (?, ?, ?, 'คน.png', ?)";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $password_hash, $phone);
        
        if (mysqli_stmt_execute($stmt)) {
            $user_id = mysqli_insert_id($con);
            
            // สร้าง session
            $_SESSION['user'] = [
                'id' => $user_id,
                'username' => $username,
                'photo' => 'คน.png'
            ];
            
            echo json_encode(['success' => true, 'message' => 'สมัครสมาชิกสำเร็จ']);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . mysqli_error($con)]);
            exit;
        }
    }
    
    elseif (isset($_POST['action']) && $_POST['action'] === 'checkUsername') {
        $username = $_POST['username'] ?? '';
        
        if (empty($username)) {
            echo json_encode(['exists' => false]);
            exit;
        }
        
        // ตรวจสอบชื่อผู้ใช้ซ้ำ
        $check = mysqli_prepare($con, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($check, "s", $username);
        mysqli_stmt_execute($check);
        $result = mysqli_stmt_get_result($check);
        
        if (mysqli_num_rows($result) > 0) {
            echo json_encode(['exists' => true]);
        } else {
            echo json_encode(['exists' => false]);
        }
        exit;
    }
    
    elseif (isset($_POST['action']) && $_POST['action'] === 'uploadPhoto') {
        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
            exit;
        }
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'ไม่มีไฟล์หรือเกิดข้อผิดพลาด']);
            exit;
        }
        $user_id = $_SESSION['user']['id'];
        $file = $_FILES['photo'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($ext), $allowed)) {
            echo json_encode(['success' => false, 'message' => 'ชนิดไฟล์ไม่สนับสนุน (ต้องใช้ jpg, png ฟื้อ)']);
            exit;
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'ไฟล์ใหญ่เกินไป (สูงสืส 2MB)']);
            exit;
        }
        $uploadDir = 'uploads/profile/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
        $filepath = $uploadDir . $filename;
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $sql = "UPDATE users SET photo = ? WHERE id = ?";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "si", $filepath, $user_id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['user']['photo'] = $filepath;
                echo json_encode(['success' => true, 'message' => 'อัพโหลดรูปสำเร็จ']);
            } else {
                @unlink($filepath);
                echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'ไม่สามารถอัพโหลดไฟล์']);
        }
        exit;
    }
    elseif (isset($_POST['action']) && $_POST['action'] === 'updateAvatar') {
        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
            exit;
        }
        
        $photo = $_POST['photo'] ?? '';
        $user_id = $_SESSION['user']['id'];
        
        if (empty($photo)) {
            echo json_encode(['success' => false, 'message' => 'ไม่มีไฟล์รูป']);
            exit;
        }
        
        // อัพเดท database
        $sql = "UPDATE users SET photo = ? WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "si", $photo, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['user']['photo'] = $photo;
            echo json_encode(['success' => true, 'message' => 'เปลี่ยนรูปสำเร็จ']);
        } else {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . mysqli_error($con)]);
        }
        exit;
    }
    elseif (isset($_POST['action']) && $_POST['action'] === 'updateProfile') {
        // update basic user information (username, email, phone)
        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
            exit;
        }
        $user_id = $_SESSION['user']['id'];
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        if ($username === '') {
            echo json_encode(['success' => false, 'message' => 'กรุณากรอกชื่อผู้ใช้']);
            exit;
        }
        
        if ($email === '') {
            echo json_encode(['success' => false, 'message' => 'กรุณากรอกอีเมล']);
            exit;
        }
        
        // check duplicate username
        $check = mysqli_prepare($con, "SELECT id FROM users WHERE username = ? AND id != ?");
        mysqli_stmt_bind_param($check, "si", $username, $user_id);
        mysqli_stmt_execute($check);
        if (mysqli_stmt_get_result($check)->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'ชื่อผู้ใช้นี้ถูกใช้งานแล้ว']);
            exit;
        }
        
        // check duplicate email
        $checkEmail = mysqli_prepare($con, "SELECT id FROM users WHERE email = ? AND id != ?");
        mysqli_stmt_bind_param($checkEmail, "si", $email, $user_id);
        mysqli_stmt_execute($checkEmail);
        if (mysqli_stmt_get_result($checkEmail)->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'อีเมลนี้ถูกใช้งานแล้ว']);
            exit;
        }
        
        // ensure phone column exists
        $colCheck = mysqli_query($con, "SHOW COLUMNS FROM users LIKE 'phone'");
        if ($colCheck && mysqli_num_rows($colCheck) === 0) {
            @mysqli_query($con, "ALTER TABLE users ADD phone VARCHAR(50) NULL");
        }
        
        $sql = "UPDATE users SET username = ?, email = ?, phone = ? WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "sssi", $username, $email, $phone, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['user']['username'] = $username;
            $_SESSION['user']['email'] = $email;
            echo json_encode(['success' => true, 'message' => 'อัปเดตข้อมูลสำเร็จ']);
        } else {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . mysqli_error($con)]);
        }
        exit;
    }
    elseif (isset($_POST['action']) && $_POST['action'] === 'changePassword') {
        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
            exit;
        }
        $user_id = $_SESSION['user']['id'];
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if ($new === '' || $confirm === '') {
            echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบ']);
            exit;
        }
        if ($new !== $confirm) {
            echo json_encode(['success' => false, 'message' => 'รหัสผ่านใหม่ไม่ตรงกัน']);
            exit;
        }
        // fetch current password hash
        $stmt = mysqli_prepare($con, "SELECT password FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        if (!$row || !password_verify($current, $row['password'])) {
            echo json_encode(['success' => false, 'message' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง']);
            exit;
        }
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $upd = mysqli_prepare($con, "UPDATE users SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($upd, "si", $newHash, $user_id);
        if (mysqli_stmt_execute($upd)) {
            echo json_encode(['success' => true, 'message' => 'เปลี่ยนรหัสผ่านสำเร็จ']);
        } else {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . mysqli_error($con)]);
        }
        exit;
    }
    //==================================================================================//
    elseif (isset($_POST['action']) && $_POST['action'] === 'searchDormitory') {

    header('Content-Type: application/json; charset=utf-8');

    $location = $_POST['location'] ?? '';
    $priceRange = $_POST['priceRange'] ?? 'all';

    if (empty($location)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกสถานที่']);
        exit;
    }

    // ===== แปลงช่วงราคา =====
    $minPrice = null;
    $maxPrice = null;

    if ($priceRange !== 'all' && strpos($priceRange, '-') !== false) {
        $range = explode('-', $priceRange);
        $minPrice = isset($range[0]) ? (int)$range[0] : null;
        $maxPrice = isset($range[1]) ? (int)$range[1] : null;
    }

    // ===== แปลงสถานที่เป็นพิกัด (รองรับพิกัดจาก POST เพื่อล็อกตำแหน่ง) =====
    $searchLat = null;
    $searchLon = null;
    $locationFound = false;

    // ถ้ามีพิกัดจาก client ให้ใช้ค่านั้นแทนการ geocode
    if (isset($_POST['searchLat']) && isset($_POST['searchLon']) && is_numeric($_POST['searchLat']) && is_numeric($_POST['searchLon'])) {
        $searchLat = (float) $_POST['searchLat'];
        $searchLon = (float) $_POST['searchLon'];
        $locationFound = true;
    } else {
        $opts = [
            'http' => [
                'method' => "GET",
                'header' => "User-Agent: DormHub/1.0\r\nAccept-Language: th\r\n",
                'timeout' => 5
            ]
        ];
        $context = stream_context_create($opts);

        // ✅ ใช้กรอบพื้นที่ที่ client ตรวจจับจากตำแหน่งผู้ใช้จริง (ถ้ามี/ถูกต้อง) ป้องกันค้นหาสถานที่ชื่อซ้ำข้ามจังหวัด
        // ถ้าไม่มี/รูปแบบผิด ให้ใช้ค่าเริ่มต้น (จังหวัดเพชรบุรี) แทน
        // viewbox = left(lon_min),top(lat_max),right(lon_max),bottom(lat_min)
        $defaultViewbox = '99.2,13.55,100.25,12.35';
        $bias = isset($_POST['bias']) ? trim($_POST['bias']) : '';
        if (!preg_match('/^-?\d+(\.\d+)?,-?\d+(\.\d+)?,-?\d+(\.\d+)?,-?\d+(\.\d+)?$/', $bias)) {
            $bias = $defaultViewbox;
        }

        $geocodeUrls = [
            "https://nominatim.openstreetmap.org/search?q=" . urlencode($location)
                . "&format=json&limit=1&countrycodes=th&viewbox=" . $bias . "&bounded=1",
            // ถ้าจำกัดแล้วไม่เจอ ค่อยลองแบบไม่จำกัดพื้นที่ (เผื่อเป็นสถานที่นอกพื้นที่จริงๆ)
            "https://nominatim.openstreetmap.org/search?q=" . urlencode($location)
                . "&format=json&limit=1&countrycodes=th",
        ];

        foreach ($geocodeUrls as $geocodeUrl) {
            $response = @file_get_contents($geocodeUrl, false, $context);
            if ($response) {
                $data = json_decode($response, true);
                if (!empty($data)) {
                    $searchLat = (float)$data[0]['lat'];
                    $searchLon = (float)$data[0]['lon'];
                    $locationFound = true;
                    break;
                }
            }
        }
    }

    // ===== ดึงข้อมูลหอพักทั้งหมด =====
    $query = "SELECT d.id, d.name, d.price, d.price_unit, d.status, d.user_id, d.water_rate, d.electricity_rate, d.contact_link, 
       l.address, l.area, l.latitude, l.longitude, 
       t.type_name AS type, 
       (SELECT image_path FROM dorm_images WHERE dorm_id = d.id AND is_main = 1 LIMIT 1) AS image, 
       (SELECT GROUP_CONCAT(facility_name SEPARATOR ',') FROM dorm_facilities WHERE dorm_id = d.id) AS facilities 
    FROM dormitories d 
    LEFT JOIN dorm_locations l ON d.id = l.dorm_id 
    LEFT JOIN dorm_types t ON d.type_id = t.id 
    WHERE d.status = 'อนุมัติแล้ว'";

    $result = mysqli_query($con, $query);

    if (!$result) {
        echo json_encode([
            'success' => false,
            'message' => mysqli_error($con)
        ]);
        exit;
    }

    $dorms = [];

    while ($row = mysqli_fetch_assoc($result)) {

        // ⭐ กรองราคา
        if ($minPrice !== null && (int)$row['price'] < $minPrice) continue;
        if ($maxPrice !== null && (int)$row['price'] > $maxPrice) continue;

        // ⭐ คำนวณระยะทาง
        if ($searchLat !== null && $searchLon !== null &&
            !empty($row['latitude']) && !empty($row['longitude'])) {

            $row['distance'] = calculateDistance(
                $searchLat,
                $searchLon,
                $row['latitude'],
                $row['longitude']
            );

        } else {
            $row['distance'] = 99999;
        }

        // row['type'] already from dorm_types.type_name
        $dorms[] = $row;
    }

    // ===== เรียงใกล้สุด =====
    usort($dorms, function($a, $b) {
        return $a['distance'] <=> $b['distance'];
    });

    // ===== ส่งข้อมูลกลับ =====
    if (count($dorms) > 0) {

        echo json_encode([
            'success' => true,
            'dorms' => $dorms,
            'searchLocation' => $location,
            'locationFound' => $locationFound,
            'searchLat' => $searchLat,
            'searchLon' => $searchLon
        ]);

    } else {

        echo json_encode([
            'success' => false,
            'message' => 'ไม่พบหอพักที่ตรงกับการค้นหา'
        ]);
    }

    exit;
}

   

    //==================================================================================//
    
    // ✅ คำแนะนำอัตโนมัติ (autocomplete): ค้นหาหอพักตรงๆ จากชื่อ/ที่อยู่/พื้นที่ใกล้เคียง
    elseif (isset($_POST['action']) && $_POST['action'] === 'searchSuggestions') {
        header('Content-Type: application/json; charset=utf-8');

        $q = isset($_POST['q']) ? trim($_POST['q']) : '';
        if (mb_strlen($q) < 2) {
            echo json_encode(['success' => true, 'dorms' => []]);
            exit;
        }

        $like = '%' . $q . '%';
        $sql = "SELECT d.id, d.name, d.price, d.price_unit,
                   l.address, l.area, l.latitude, l.longitude
                FROM dormitories d
                LEFT JOIN dorm_locations l ON d.id = l.dorm_id
                WHERE d.status = 'อนุมัติแล้ว'
                  AND (d.name LIKE ? OR l.address LIKE ? OR l.area LIKE ?)
                ORDER BY d.name ASC
                LIMIT 6";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $like, $like, $like);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        $dorms = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $dorms[] = $row;
        }

        echo json_encode(['success' => true, 'dorms' => $dorms], JSON_UNESCAPED_UNICODE);
        exit;
    }

    elseif (isset($_POST['action']) && $_POST['action'] === 'getAllDormitories') {
        // ดึงหอพักทั้งหมด พร้อมพิกัด (สำหรับแสดงหมุดบนแมพตอนเปิดหน้า)
        $query = "SELECT d.id, d.name, d.price, d.price_unit, d.status, d.user_id, d.water_rate, d.electricity_rate, d.contact_link, 
            l.address, l.area, l.latitude, l.longitude, 
            t.type_name AS type, 
            (SELECT image_path FROM dorm_images WHERE dorm_id = d.id AND is_main = 1 LIMIT 1) AS image, 
            (SELECT GROUP_CONCAT(facility_name SEPARATOR ',') FROM dorm_facilities WHERE dorm_id = d.id) AS facilities 
            FROM dormitories d 
            LEFT JOIN dorm_locations l ON d.id = l.dorm_id 
            LEFT JOIN dorm_types t ON d.type_id = t.id 
            WHERE l.latitude IS NOT NULL AND l.longitude IS NOT NULL AND d.status = 'อนุมัติแล้ว'";
        $result = mysqli_query($con, $query);
        
        $dorms = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $dorms[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'dorms' => $dorms
        ]);
        exit;
    }
    
    // ดึงรายละเอียดหอพักตาม id (สำหรับ modal รายละเอียด)
    elseif (isset($_POST['action']) && $_POST['action'] === 'getDormById') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid id']);
            exit;
        }

        $query = "SELECT d.id, d.name, d.price, d.price_unit, d.status, d.user_id, d.water_rate, d.electricity_rate, d.contact_link, 
            l.address, l.area, l.latitude, l.longitude, 
            t.type_name AS type, 
            (SELECT image_path FROM dorm_images WHERE dorm_id = d.id AND is_main = 1 LIMIT 1) AS image, 
            (SELECT GROUP_CONCAT(image_path SEPARATOR ',') FROM dorm_images WHERE dorm_id = d.id AND is_main = 0) AS gallery, 
            (SELECT GROUP_CONCAT(facility_name SEPARATOR ',') FROM dorm_facilities WHERE dorm_id = d.id) AS facilities 
            FROM dormitories d 
            LEFT JOIN dorm_locations l ON d.id = l.dorm_id 
            LEFT JOIN dorm_types t ON d.type_id = t.id 
            WHERE d.id = " . $id . " AND d.status = 'อนุมัติแล้ว'";
        $res = mysqli_query($con, $query);
        if (!$res) {
            echo json_encode(['success' => false, 'message' => mysqli_error($con)]);
            exit;
        }
        $row = mysqli_fetch_assoc($res);
        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'หอพักไม่พบ']);
            exit;
        }

        // เพิ่มยอดเข้าชม (View Counter)
        mysqli_query($con, "UPDATE dormitories SET view_count = view_count + 1 WHERE id = " . $id);

        // เติม d.images ให้ JS ใช้งาน gallery ได้เหมือนเดิม (search-dorm.js คาดว่าจะ parse JSON)
        if (!empty($row['gallery'])) {
            $galleryArr = array_filter(array_map('trim', explode(',', $row['gallery'])));
            $row['images'] = json_encode(array_values($galleryArr), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } else {
            $row['images'] = $row['image'] ? json_encode([$row['image']], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '[]';
        }

        echo json_encode(['success' => true, 'dorm' => $row]);
        exit;
    }
    
    elseif (isset($_POST['action']) && $_POST['action'] === 'addDormitory') {
        $name = $_POST['name'] ?? '';
        $address = $_POST['address'] ?? '';
        $area = $_POST['area'] ?? '';
        $price = $_POST['price'] ?? '';
        $type = $_POST['type'] ?? '';
        $facilities = $_POST['facilities'] ?? '';
        $latitude = $_POST['latitude'] ?? '';
        $longitude = $_POST['longitude'] ?? '';
        $image = $_POST['image'] ?? '';
        
        // ตรวจสอบข้อมูล
        if (empty($name) || empty($address) || empty($area) || empty($price) || empty($latitude) || empty($longitude)) {
            echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบทุกช่อง (*)']);
            exit;
        }
        
        // ตรวจสอบชื่อหอพักซ้ำ
        $check = mysqli_prepare($con, "SELECT id FROM dormitories WHERE name = ?");
        mysqli_stmt_bind_param($check, "s", $name);
        mysqli_stmt_execute($check);
        if (mysqli_stmt_get_result($check)->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'หอพักนี้มีอยู่ในระบบแล้ว']);
            exit;
        }
        
        // คำนวณระยะทาง (ระยะทางจาก Phetchaburi center)
        $bangkokLat = 13.1171;
        $bangkokLng = 99.9440;
        $distance = sqrt(pow($latitude - $bangkokLat, 2) + pow($longitude - $bangkokLng, 2)) * 111; // ประมาณ
        
        // ตรวจสอบว่าเป็นผู้ใช้ที่ล็อกอินก่อน
        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบก่อนเพิ่มหอพัก']);
            exit;
        }
        $user_id = intval($_SESSION['user']['id']);

        // แปลงชนิดข้อมูลให้ถูกต้อง
        $price = (int)$price;
        $type = (int)$type;
        $latitude = (float)$latitude;
        $longitude = (float)$longitude;
        $distance = (float)$distance;

        // บันทึกลงระบบฐานข้อมูลใหม่ (Normalization)
        $stmt = mysqli_prepare($con, "INSERT INTO dormitories (user_id, type_id, name, price, water_rate, electricity_rate, contact_link, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'รอการอนุมัติ')");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . mysqli_error($con)]);
            exit;
        }

        $water_rate = floatval($_POST['water_rate'] ?? 0);
        $electricity_rate = floatval($_POST['electricity_rate'] ?? 0);

        mysqli_stmt_bind_param($stmt, "iisddss", $user_id, $type, $name, $price, $water_rate, $electricity_rate, $contact_link);
        if (!mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . mysqli_error($con)]);
            exit;
        }

        $dorm_id = mysqli_insert_id($con);

        // insert location
        $stmtLoc = mysqli_prepare($con, "INSERT INTO dorm_locations (dorm_id, address, area, latitude, longitude) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmtLoc, "issdd", $dorm_id, $address, $area, $latitude, $longitude);
        mysqli_stmt_execute($stmtLoc);

        // insert facilities
        $facilityItems = preg_split('/\s*,\s*/u', trim($facilities));
        foreach ($facilityItems as $item) {
            $item = trim($item);
            if ($item === '') continue;
            $stmtFac = mysqli_prepare($con, "INSERT INTO dorm_facilities (dorm_id, facility_name) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmtFac, "is", $dorm_id, $item);
            mysqli_stmt_execute($stmtFac);
        }

        // insert image as main image
        if (!empty($image)) {
            $stmtImg = mysqli_prepare($con, "INSERT INTO dorm_images (dorm_id, image_path, is_main) VALUES (?, ?, 1)");
            mysqli_stmt_bind_param($stmtImg, "is", $dorm_id, $image);
            mysqli_stmt_execute($stmtImg);
        }

        echo json_encode(['success' => true, 'message' => 'เพิ่มหอพักสำเร็จ']);
        exit;
    }
    
    elseif (isset($_POST['action']) && $_POST['action'] === 'forgotPassword') {
        $email = $_POST['email'] ?? '';
        
        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'กรุณากรอกอีเมล']);
            exit;
        }
        
        // ค้นหา user จากอีเมล
        $check = mysqli_prepare($con, "SELECT id, username FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        $result = mysqli_stmt_get_result($check);
        
        if (mysqli_num_rows($result) === 0) {
            echo json_encode(['success' => false, 'message' => 'ไม่พบอีเมลนี้ในระบบ']);
            exit;
        }
        
        $user = mysqli_fetch_assoc($result);
        
        // สร้าง token รีเซ็ตรหัสผ่าน
        $token = bin2hex(random_bytes(32));
        $tokenExpire = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // บันทึก token
        $updateToken = mysqli_prepare($con, "UPDATE users SET reset_token = ?, reset_token_expire = ? WHERE id = ?");
        mysqli_stmt_bind_param($updateToken, "ssi", $token, $tokenExpire, $user['id']);
        mysqli_stmt_execute($updateToken);
        
        // *** ส่งอีเมล (ตอนนี้เป็นแค่ข้อความ) ***
        // ในการใช้งานจริงต้องใช้ PHPMailer หรือ mail()
        $resetLink = "http://localhost/project/reset-password.php?token=$token&email=" . urlencode($email);
        
        // ข้อความทั่วไป
        echo json_encode([
            'success' => true, 
            'message' => 'ลิงก์รีเซ็ตรหัสผ่านถูกส่งไปยังอีเมลของคุณแล้ว'
        ]);
        exit;
    }

    //==================================================================================//
    // การแจ้งเตือน
    elseif ($_POST['action'] === 'getNotifications') {
        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
            exit;
        }
        $user_id = intval($_SESSION['user']['id']);
        $stmt = mysqli_prepare($con, "SELECT id, message, is_read, created_at FROM notifications
            WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $notifs = [];
        while ($n = mysqli_fetch_assoc($res)) {
            $n['created_at'] = date('d/m/Y H:i', strtotime($n['created_at']));
            $notifs[] = $n;
        }
        echo json_encode(['success' => true, 'notifications' => $notifs]);
        exit;
    }

    elseif ($_POST['action'] === 'markNotificationsRead') {
        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false]);
            exit;
        }
        $user_id = intval($_SESSION['user']['id']);
        mysqli_query($con, "UPDATE notifications SET is_read = 1 WHERE user_id = " . $user_id);
        echo json_encode(['success' => true]);
        exit;
    }
}
?>