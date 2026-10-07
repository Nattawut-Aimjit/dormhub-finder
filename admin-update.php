<?php
session_start();
include "connect.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: viewhomepage.php");
    exit();
}

if (!isset($_GET['id']) || !isset($_GET['status'])) {
    die("ข้อมูลไม่ครบ");
}

$id = intval($_GET['id']);
$status = $_GET['status'];
$reason = isset($_GET['reason']) ? trim($_GET['reason']) : '';

if ($status == "approve") {
    $newStatus = "อนุมัติแล้ว";
    $reason = ''; // อนุมัติแล้วไม่ต้องมีเหตุผล
} elseif ($status == "reject") {
    $newStatus = "ถูกปฏิเสธ";
} else {
    die("สถานะไม่ถูกต้อง");
}

// แก้จาก dormitory -> dormitories (ตารางจริงที่ระบบใช้งาน) และเพิ่ม reject_reason
$sql = "UPDATE dormitories SET status=?, reject_reason=? WHERE id=?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "ssi", $newStatus, $reason, $id);
mysqli_stmt_execute($stmt);

// ดึงเจ้าของที่พัก เพื่อส่งการแจ้งเตือน
$ownerRes = mysqli_query($con, "SELECT user_id, name FROM dormitories WHERE id=" . $id);
$ownerRow = mysqli_fetch_assoc($ownerRes);
if ($ownerRow) {
    $notifMsg = $status == "approve"
        ? "ที่พัก \"" . $ownerRow['name'] . "\" ของคุณได้รับการอนุมัติแล้ว"
        : "ที่พัก \"" . $ownerRow['name'] . "\" ของคุณถูกปฏิเสธ" . (!empty($reason) ? " เหตุผล: " . $reason : "");
    $stmtN = mysqli_prepare($con, "INSERT INTO notifications (user_id, dorm_id, message) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmtN, "iis", $ownerRow['user_id'], $id, $notifMsg);
    mysqli_stmt_execute($stmtN);
}

header("Location: admin-Status.php");
exit();
?>