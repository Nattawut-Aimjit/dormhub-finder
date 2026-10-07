<?php
session_start();
include "connect.php";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id = $_SESSION['user']['id'];
    $name = isset($_POST['name']) ? mysqli_real_escape_string($con, $_POST['name']) : '';
    $address = isset($_POST['address']) ? mysqli_real_escape_string($con, $_POST['address']) : '';
    $area = isset($_POST['area']) ? mysqli_real_escape_string($con, $_POST['area']) : '';
    $price = isset($_POST['price']) ? mysqli_real_escape_string($con, $_POST['price']) : '';
    $price_unit = (isset($_POST['price_unit']) && $_POST['price_unit'] === 'วัน') ? 'วัน' : 'เดือน';
    $type = isset($_POST['type']) ? mysqli_real_escape_string($con, $_POST['type']) : '';
    $facilities = isset($_POST['facilities']) ? mysqli_real_escape_string($con, $_POST['facilities']) : '';
    $latitude = isset($_POST['latitude']) ? mysqli_real_escape_string($con, $_POST['latitude']) : '';
    $longitude = isset($_POST['longitude']) ? mysqli_real_escape_string($con, $_POST['longitude']) : '';
    $contact_link = isset($_POST['contact_link']) ? mysqli_real_escape_string($con, $_POST['contact_link']) : '';
    $water_rate = isset($_POST['water_rate']) ? mysqli_real_escape_string($con, $_POST['water_rate']) : '0';
    $electricity_rate = isset($_POST['electricity_rate']) ? mysqli_real_escape_string($con, $_POST['electricity_rate']) : '0';

    

    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($name) || empty($address) || empty($area) || empty($price) || empty($type) || empty($facilities) || empty($contact_link)) {
        echo "กรุณากรอกข้อมูลให้ครบถ้วน";
        exit;
    }

//-----------------------------------------
    // Create folder for images
    $folder = rand();
    $basePath = "documents/".$folder;
    mkdir($basePath, 0777, true);

    // Handle main image upload
    $mainImagePath = '';
    if (isset($_FILES['main_image']) && isset($_FILES['main_image']['error']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
        $orig = $_FILES['main_image']['name'];
        $safe = time() . '_main_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($orig));
        $dest = $basePath . '/' . $safe;
        if (move_uploaded_file($_FILES['main_image']['tmp_name'], $dest)) {
            $mainImagePath = $dest;
        }
    }

    // Handle gallery images upload
    $galleryImages = array();
    if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
        foreach ($_FILES['images']['name'] as $i => $originalName) {
            if (empty($originalName)) continue;
            if (isset($_FILES['images']['error'][$i]) && $_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $tmp = $_FILES['images']['tmp_name'][$i];
                $safeName = time() . '_' . $i . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($originalName));
                $destPath = $basePath . '/' . $safeName;
                if (move_uploaded_file($tmp, $destPath)) {
                    $galleryImages[] = $destPath;
                }
            }
        }
    }

    // If no main image but gallery has images, use first as main
    if (empty($mainImagePath) && count($galleryImages) > 0) {
        $mainImagePath = $galleryImages[0];
    }

    // Prepare images JSON for gallery
    $imagesJson = '';
    if (count($galleryImages) > 0) {
        $imagesJson = json_encode($galleryImages, JSON_UNESCAPED_SLASHES);
    }

    // Ensure images column exists
    $colCheck = mysqli_query($con, "SHOW COLUMNS FROM dormitory LIKE 'images'");
    if ($colCheck && mysqli_num_rows($colCheck) === 0) {
        @mysqli_query($con, "ALTER TABLE dormitory ADD images TEXT NULL");
    }

    // Check if contact_link column exists; if not, create it
    $colCheck = mysqli_query($con, "SHOW COLUMNS FROM dormitory LIKE 'contact_link'");
    if ($colCheck && mysqli_num_rows($colCheck) === 0) {
        @mysqli_query($con, "ALTER TABLE dormitory ADD contact_link VARCHAR(500) DEFAULT ''");
    }

    // Check if water_rate column exists; if not, create it
    $colCheck = mysqli_query($con, "SHOW COLUMNS FROM dormitory LIKE 'water_rate'");
    if ($colCheck && mysqli_num_rows($colCheck) === 0) {
        @mysqli_query($con, "ALTER TABLE dormitory ADD water_rate DECIMAL(8,2) DEFAULT 0");
    }

    // Check if electricity_rate column exists; if not, create it
    $colCheck = mysqli_query($con, "SHOW COLUMNS FROM dormitory LIKE 'electricity_rate'");
    if ($colCheck && mysqli_num_rows($colCheck) === 0) {
        @mysqli_query($con, "ALTER TABLE dormitory ADD electricity_rate DECIMAL(8,2) DEFAULT 0");
    }

    // Check if price_unit column exists on dormitories; if not, create it
    $colCheck = mysqli_query($con, "SHOW COLUMNS FROM dormitories LIKE 'price_unit'");
    if ($colCheck && mysqli_num_rows($colCheck) === 0) {
        @mysqli_query($con, "ALTER TABLE dormitories ADD price_unit ENUM('เดือน','วัน') NOT NULL DEFAULT 'เดือน' AFTER price");
    }

    // ที่พักรายวัน/รายคืนมักรวมค่าน้ำ-ไฟไว้ในราคาแล้ว ไม่ต้องคิดแยกตามหน่วย
    if ($price_unit === 'วัน') {
        $water_rate = '0';
        $electricity_rate = '0';
    }
//-----------------------------------------

    // บันทึกข้อมูลในระบบฐานข้อมูลใหม่ (Normalization)
    $stmt = mysqli_prepare($con, "INSERT INTO dormitories (user_id, type_id, name, price, price_unit, water_rate, electricity_rate, contact_link, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'รอการอนุมัติ')");
    mysqli_stmt_bind_param($stmt, "iisdsdss", $user_id, $type, $name, $price, $price_unit, $water_rate, $electricity_rate, $contact_link);
    if (!mysqli_stmt_execute($stmt)) {
        die('เกิดข้อผิดพลาด insert dormitories: ' . mysqli_error($con));
    }

    $dorm_id = mysqli_insert_id($con);

    // dorm_locations
    $stmt = mysqli_prepare($con, "INSERT INTO dorm_locations (dorm_id, address, area, latitude, longitude) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issdd", $dorm_id, $address, $area, $latitude, $longitude);
    if (!mysqli_stmt_execute($stmt)) {
        die('เกิดข้อผิดพลาด insert dorm_locations: ' . mysqli_error($con));
    }

    // dorm_facilities
    $facilityItems = preg_split('/\s*,\s*/u', trim($facilities));
    foreach ($facilityItems as $item) {
        $item = trim($item);
        if ($item === '') continue;
        $stmt = mysqli_prepare($con, "INSERT INTO dorm_facilities (dorm_id, facility_name) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "is", $dorm_id, $item);
        mysqli_stmt_execute($stmt);
    }

    // dorm_images
    if (!empty($mainImagePath)) {
        $stmt = mysqli_prepare($con, "INSERT INTO dorm_images (dorm_id, image_path, is_main) VALUES (?, ?, 1)");
        mysqli_stmt_bind_param($stmt, "is", $dorm_id, $mainImagePath);
        mysqli_stmt_execute($stmt);
    }

    foreach ($galleryImages as $galleryPath) {
        if (empty($galleryPath)) continue;
        $stmt = mysqli_prepare($con, "INSERT INTO dorm_images (dorm_id, image_path, is_main) VALUES (?, ?, 0)");
        mysqli_stmt_bind_param($stmt, "is", $dorm_id, $galleryPath);
        mysqli_stmt_execute($stmt);
    }

    header("Location: Place-Me.php");
    exit;
}

mysqli_close($con);
?>