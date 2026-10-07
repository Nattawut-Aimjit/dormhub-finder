<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'owner') {
    header("Location: viewhomepage.php");
    exit();
}
include "connect.php";
$user_id = intval($_SESSION['user']['id']);

/* กันเหนียว: เผื่อยังไม่ได้รัน migration_new_features.sql */
$colCheck = mysqli_query($con, "SHOW COLUMNS FROM dormitories LIKE 'view_count'");
if ($colCheck && mysqli_num_rows($colCheck) === 0) {
    @mysqli_query($con, "ALTER TABLE dormitories ADD view_count INT NOT NULL DEFAULT 0");
}
$colCheck = mysqli_query($con, "SHOW COLUMNS FROM dormitories LIKE 'reject_reason'");
if ($colCheck && mysqli_num_rows($colCheck) === 0) {
    @mysqli_query($con, "ALTER TABLE dormitories ADD reject_reason VARCHAR(500) NULL");
}

/* ---------- สรุปตัวเลขของฉัน ---------- */
$stmt = mysqli_prepare($con, "SELECT COUNT(*) c FROM dormitories WHERE user_id=?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$myTotal = mysqli_stmt_get_result($stmt)->fetch_assoc()['c'];

$stmt = mysqli_prepare($con, "SELECT COUNT(*) c FROM dormitories WHERE user_id=? AND status='รอการอนุมัติ'");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$myPending = mysqli_stmt_get_result($stmt)->fetch_assoc()['c'];

$stmt = mysqli_prepare($con, "SELECT COUNT(*) c FROM dormitories WHERE user_id=? AND status='อนุมัติแล้ว'");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$myApproved = mysqli_stmt_get_result($stmt)->fetch_assoc()['c'];

$stmt = mysqli_prepare($con, "SELECT COUNT(*) c FROM dormitories WHERE user_id=? AND status='ถูกปฏิเสธ'");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$myRejected = mysqli_stmt_get_result($stmt)->fetch_assoc()['c'];

$stmt = mysqli_prepare($con, "SELECT COALESCE(SUM(view_count),0) c FROM dormitories WHERE user_id=?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$myViews = mysqli_stmt_get_result($stmt)->fetch_assoc()['c'];

/* ---------- รายการที่พักของฉัน เรียงตามยอดวิว ---------- */
$stmt = mysqli_prepare($con, "SELECT d.id, d.name, d.status, d.view_count, d.reject_reason
    FROM dormitories d WHERE d.user_id=? ORDER BY d.view_count DESC");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$myDorms = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>แดชบอร์ดของฉัน - DormHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="style.css">
<style>
    body{background:#f4f7f9;}
    .dash-wrap{max-width:1100px;margin:30px auto;padding:0 15px;}
    .stat-card{background:#fff;border-radius:12px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,.06);height:100%;}
    .stat-num{font-size:26px;font-weight:700;color:#17324A;}
    .stat-label{color:#6b7c85;font-size:13px;}
    .table-card{background:#fff;border-radius:12px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,.06);margin-top:20px;}
    .reason-box{background:#fdecea;color:#c0392b;border-radius:8px;padding:6px 10px;font-size:12.5px;}
</style>
</head>
<body>
<header class="header">
   <div class="header-left">
        <a href="Place-Me.php"><img src="รูป/Dormhub-no.png" alt="Dormhub" class="logo"></a>
        <span class="site-name">Dormhub</span>
    </div>

    <button class="nav-toggle" id="navToggle" aria-label="เมนู" onclick="toggleMobileNav()">
        <span></span><span></span><span></span>
    </button>
    <div class="nav-overlay" id="navOverlay" onclick="closeMobileNav()"></div>

    <div class="header-right" id="headerRight">
        <nav class="menu">
            <a href="homepage.php">กลับสู่หน้าหลัก</a>
            <a href="Place-Me.php">ที่พักของฉัน</a>
            <a href="owner-dashboard.php" class="active">แดชบอร์ด</a>
        </nav>
        <?php include "profile.php"; ?>
    </div>
</header>

<div class="dash-wrap">
    <h4 class="mb-3">แดชบอร์ดของฉัน</h4>

    <div class="row g-3">
        <div class="col-6 col-md-2">
            <div class="stat-card"><div class="stat-num"><?= $myTotal ?></div><div class="stat-label">ที่พักของฉัน</div></div>
        </div>
        <div class="col-6 col-md-2">
            <div class="stat-card"><div class="stat-num text-warning"><?= $myPending ?></div><div class="stat-label">รอการอนุมัติ</div></div>
        </div>
        <div class="col-6 col-md-2">
            <div class="stat-card"><div class="stat-num text-success"><?= $myApproved ?></div><div class="stat-label">อนุมัติแล้ว</div></div>
        </div>
        <div class="col-6 col-md-2">
            <div class="stat-card"><div class="stat-num text-danger"><?= $myRejected ?></div><div class="stat-label">ถูกปฏิเสธ</div></div>
        </div>
        <div class="col-6 col-md-2">
            <div class="stat-card"><div class="stat-num"><?= number_format($myViews) ?></div><div class="stat-label">ยอดเข้าชมรวม</div></div>
        </div>
    </div>

    <div class="table-card">
        <h6 class="mb-3">ที่พักของฉันทั้งหมด</h6>
        <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr class="table-light">
                    <th>ชื่อที่พัก</th>
                    <th class="text-center">สถานะ</th>
                    <th class="text-center">ยอดเข้าชม</th>
                </tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($myDorms) === 0): ?>
                <tr><td colspan="3" class="text-center text-muted">คุณยังไม่มีที่พักในระบบ</td></tr>
            <?php else: while ($d = mysqli_fetch_assoc($myDorms)): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($d['name']) ?>
                        <?php if ($d['status'] === 'ถูกปฏิเสธ' && !empty($d['reject_reason'])): ?>
                            <div class="reason-box mt-1">เหตุผลที่ถูกปฏิเสธ: <?= htmlspecialchars($d['reject_reason']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($d['status']==='รอการอนุมัติ'): ?><span class="badge bg-warning">รอการอนุมัติ</span>
                        <?php elseif ($d['status']==='อนุมัติแล้ว'): ?><span class="badge bg-success">อนุมัติแล้ว</span>
                        <?php else: ?><span class="badge bg-danger">ถูกปฏิเสธ</span><?php endif; ?>
                    </td>
                    <td class="text-center"><?= number_format($d['view_count']) ?></td>
                </tr>
            <?php endwhile; endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<script>
function toggleMobileNav(){
    document.getElementById("navToggle").classList.toggle("open");
    document.getElementById("headerRight").classList.toggle("open");
    document.getElementById("navOverlay").classList.toggle("show");
}
function closeMobileNav(){
    document.getElementById("navToggle").classList.remove("open");
    document.getElementById("headerRight").classList.remove("open");
    document.getElementById("navOverlay").classList.remove("show");
}
document.addEventListener("click", function(e){
    if (e.target.closest && e.target.closest(".menu a")) closeMobileNav();
});
</script>
<script>
function toggleProfileMenu(){
    const menu = document.getElementById("profile-menu");
    menu.classList.toggle("show");
}

document.addEventListener("click", function(e){
    const profile = document.querySelector(".profile");
    const menu = document.getElementById("profile-menu");
    if (menu && !profile.contains(e.target)) {
        menu.classList.remove("show");
    }
});
</script>


    
<script>
function toggleMobileNav(){
    document.getElementById("navToggle").classList.toggle("open");
    document.getElementById("headerRight").classList.toggle("open");
    document.getElementById("navOverlay").classList.toggle("show");
}
function closeMobileNav(){
    document.getElementById("navToggle").classList.remove("open");
    document.getElementById("headerRight").classList.remove("open");
    document.getElementById("navOverlay").classList.remove("show");
}
document.addEventListener("click", function(e){
    if (e.target.closest && e.target.closest(".menu a")) closeMobileNav();
});
</script>
</body>
</html>
