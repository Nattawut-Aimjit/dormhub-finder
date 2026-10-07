<?php

include "connect.php";

$id = intval($_POST["id"]);
$name = $_POST["name"] ?? '';
$address = $_POST["address"] ?? '';
$area = $_POST["area"] ?? '';
$price = floatval($_POST["price"] ?? 0);
$price_unit = (isset($_POST['price_unit']) && $_POST['price_unit'] === 'วัน') ? 'วัน' : 'เดือน';
$type = intval($_POST["type"] ?? 0);
$facilities = $_POST["facilities"] ?? '';
$latitude = floatval($_POST["latitude"] ?? 0);
$longitude = floatval($_POST["longitude"] ?? 0);
$contact_link = $_POST["contact_link"] ?? '';
$water_rate = isset($_POST['water_rate']) ? floatval($_POST['water_rate']) : 0;
$electricity_rate = isset($_POST['electricity_rate']) ? floatval($_POST['electricity_rate']) : 0;

// ที่พักรายวัน/รายคืนมักรวมค่าน้ำ-ไฟไว้ในราคาแล้ว ไม่ต้องคิดแยกตามหน่วย
if ($price_unit === 'วัน') {
    $water_rate = 0;
    $electricity_rate = 0;
}

if ($id <= 0) {
    die('Invalid dormitory id');
}

// Load existing images from normalized table
$currentMain = '';
$currentImages = [];
$res = mysqli_query($con, "SELECT image_path FROM dorm_images WHERE dorm_id={$id} AND is_main=1 LIMIT 1");
if ($res && $row = mysqli_fetch_assoc($res)) {
    $currentMain = $row['image_path'];
}

$res = mysqli_query($con, "SELECT image_path FROM dorm_images WHERE dorm_id={$id} AND is_main=0");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $currentImages[] = $row['image_path'];
    }
}

// Determine target folder
$targetFolder = '';
if (!empty($currentMain)) {
    $parts = explode('/', $currentMain);
    if (count($parts) >= 2) {
        $targetFolder = $parts[0] . '/' . $parts[1];
    }
}
if (empty($targetFolder)) {
    $rand = rand();
    $targetFolder = 'documents/' . $rand;
}
if (!is_dir($targetFolder)) mkdir($targetFolder, 0777, true);

// Replace images and updates
if (!empty($_POST['delete_all_images'])) {
    foreach ($currentImages as $imgPath) {
        if (file_exists($imgPath)) @unlink($imgPath);
    }
    if (!empty($currentMain) && file_exists($currentMain)) @unlink($currentMain);
    $currentImages = [];
    $currentMain = '';
}

if (isset($_POST['delete_images']) && is_array($_POST['delete_images'])) {
    foreach ($_POST['delete_images'] as $del) {
        $idx = array_search($del, $currentImages);
        if ($idx !== false) unset($currentImages[$idx]);
        if (file_exists($del)) @unlink($del);
        if ($del === $currentMain) $currentMain = '';
    }
    $currentImages = array_values($currentImages);
}

if (isset($_FILES['main_image']) && isset($_FILES['main_image']['error']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
    $orig = $_FILES['main_image']['name'];
    $safe = time() . '_main_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($orig));
    $dest = $targetFolder . '/' . $safe;
    if (move_uploaded_file($_FILES['main_image']['tmp_name'], $dest)) {
        if (!empty($currentMain) && file_exists($currentMain)) @unlink($currentMain);
        $currentMain = $dest;
    }
}

if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
    foreach ($_FILES['images']['name'] as $i => $originalName) {
        if (empty($originalName)) continue;
        if (isset($_FILES['images']['error'][$i]) && $_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
            $tmp = $_FILES['images']['tmp_name'][$i];
            $safeName = time() . '_' . $i . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($originalName));
            $destPath = $targetFolder . '/' . $safeName;
            if (move_uploaded_file($tmp, $destPath)) {
                $currentImages[] = $destPath;
            }
        }
    }
}

if (empty($currentMain) && count($currentImages) > 0) {
    $currentMain = $currentImages[0];
}

// Update parent table
$stmt = mysqli_prepare($con, "UPDATE dormitories SET name=?, price=?, price_unit=?, type_id=?, water_rate=?, electricity_rate=?, contact_link=? WHERE id=?");
mysqli_stmt_bind_param($stmt, "sdsiddsi", $name, $price, $price_unit, $type, $water_rate, $electricity_rate, $contact_link, $id);
if (!mysqli_stmt_execute($stmt)) {
    die('เกิดข้อผิดพลาด update dormitories: ' . mysqli_error($con));
}

// Update location
$stmt = mysqli_prepare($con, "UPDATE dorm_locations SET address=?, area=?, latitude=?, longitude=? WHERE dorm_id=?");
mysqli_stmt_bind_param($stmt, "ssddi", $address, $area, $latitude, $longitude, $id);
mysqli_stmt_execute($stmt);

// Update facilities (delete+insert)
mysqli_query($con, "DELETE FROM dorm_facilities WHERE dorm_id={$id}");
$facilityItems = preg_split('/\s*,\s*/u', trim($facilities));
foreach ($facilityItems as $item) {
    $item = trim($item);
    if ($item === '') continue;
    $stmt = mysqli_prepare($con, "INSERT INTO dorm_facilities (dorm_id, facility_name) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "is", $id, $item);
    mysqli_stmt_execute($stmt);
}

// Update images rows
mysqli_query($con, "DELETE FROM dorm_images WHERE dorm_id={$id} AND (is_main=0 OR is_main=1)");
if (!empty($currentMain)) {
    $stmt = mysqli_prepare($con, "INSERT INTO dorm_images (dorm_id, image_path, is_main) VALUES (?, ?, 1)");
    mysqli_stmt_bind_param($stmt, "is", $id, $currentMain);
    mysqli_stmt_execute($stmt);
}
foreach ($currentImages as $imgPath) {
    if (empty($imgPath)) continue;
    // skip if main duplicates
    if ($imgPath === $currentMain) continue;
    $stmt = mysqli_prepare($con, "INSERT INTO dorm_images (dorm_id, image_path, is_main) VALUES (?, ?, 0)");
    mysqli_stmt_bind_param($stmt, "is", $id, $imgPath);
    mysqli_stmt_execute($stmt);
}

header("Location: Place-Me.php");
exit;
?>
    
