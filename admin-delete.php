<?php
session_start();
if (
    !isset($_SESSION['user']) ||
    ($_SESSION['user']['role'] !== 'owner' && $_SESSION['user']['role'] !== 'admin')
) {
    header("Location: viewhomepage.php");
    exit();
}

include "connect.php";

/* ==========================
   1. เช็คสิทธิ์ก่อน
========================== */
if (!isset($_GET['id'])) {
    die("ไม่พบข้อมูล");
}

$id = intval($_GET['id']);
$user_id = $_SESSION['user']['id'];

/* ==========================
   2. เช็คว่าเป็นของ user จริงไหม
========================== */
if ($_SESSION['user']['role'] === 'admin') {

    // admin ไม่ต้องเช็ค user_id
    $sql = "SELECT id FROM dormitories WHERE id=?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);

} else {

    // owner ต้องเป็นของตัวเอง
    $sql = "SELECT id FROM dormitories WHERE id=? AND user_id=?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);

}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    die("คุณไม่มีสิทธิ์ลบรายการนี้");
}

$baseFolder = '';
$imageRes = mysqli_query($con, "SELECT image_path FROM dorm_images WHERE dorm_id = " . $id);
while ($imgRow = mysqli_fetch_assoc($imageRes)) {
    $imgPath = $imgRow['image_path'];
    if (!empty($imgPath) && file_exists($imgPath)) {
        unlink($imgPath);
    }
    if (empty($baseFolder) && !empty($imgPath)) {
        $baseFolder = dirname($imgPath);
    }
}

if (!empty($baseFolder) && is_dir($baseFolder) && count(scandir($baseFolder)) == 2) {
    rmdir($baseFolder);
}

/* ==========================
   4. ลบข้อมูลในฐานข้อมูล
========================== */
if ($_SESSION['user']['role'] === 'admin') {

    $sqlDelete = "DELETE FROM dormitories WHERE id=?";
    $stmtDelete = mysqli_prepare($con, $sqlDelete);
    mysqli_stmt_bind_param($stmtDelete, "i", $id);

} else {

    $sqlDelete = "DELETE FROM dormitories WHERE id=? AND user_id=?";
    $stmtDelete = mysqli_prepare($con, $sqlDelete);
    mysqli_stmt_bind_param($stmtDelete, "ii", $id, $user_id);

}

mysqli_stmt_execute($stmtDelete);



/* ==========================
   5. Redirect
========================== */
header("Location: admin-Status.php");
exit();
