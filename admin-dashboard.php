<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: viewhomepage.php");
    exit();
}
include "connect.php";

/* กันเหนียว: เผื่อยังไม่ได้รัน migration_new_features.sql */
$colCheck = mysqli_query($con, "SHOW COLUMNS FROM dormitories LIKE 'view_count'");
if ($colCheck && mysqli_num_rows($colCheck) === 0) {
    @mysqli_query($con, "ALTER TABLE dormitories ADD view_count INT NOT NULL DEFAULT 0");
}
$colCheck = mysqli_query($con, "SHOW COLUMNS FROM dormitories LIKE 'created_at'");
if ($colCheck && mysqli_num_rows($colCheck) === 0) {
    @mysqli_query($con, "ALTER TABLE dormitories ADD created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
}

/* ---------- สรุปตัวเลขหลัก ---------- */
$total = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) c FROM dormitories"))['c'];
$pending = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) c FROM dormitories WHERE status='รอการอนุมัติ'"))['c'];
$approved = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) c FROM dormitories WHERE status='อนุมัติแล้ว'"))['c'];
$rejected = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) c FROM dormitories WHERE status='ถูกปฏิเสธ'"))['c'];
$totalUsers = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) c FROM users WHERE role='owner'"))['c'];
$totalViews = mysqli_fetch_assoc(mysqli_query($con, "SELECT COALESCE(SUM(view_count),0) c FROM dormitories"))['c'];

/* ---------- สัดส่วนตามประเภทที่พัก ---------- */
$typeRows = mysqli_query($con, "SELECT t.type_name, COUNT(d.id) cnt
    FROM dorm_types t LEFT JOIN dormitories d ON d.type_id = t.id AND d.status='อนุมัติแล้ว'
    GROUP BY t.id, t.type_name ORDER BY t.id");
$typeLabels = [];
$typeCounts = [];
while ($r = mysqli_fetch_assoc($typeRows)) {
    $typeLabels[] = $r['type_name'];
    $typeCounts[] = (int)$r['cnt'];
}

/* ---------- ที่พักเพิ่มเข้ามาต่อเดือน (6 เดือนล่าสุด) ---------- */
$monthRows = mysqli_query($con, "SELECT DATE_FORMAT(created_at, '%Y-%m') ym, COUNT(*) cnt
    FROM dormitories
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY ym ORDER BY ym");
$monthLabels = [];
$monthCounts = [];
while ($r = mysqli_fetch_assoc($monthRows)) {
    $monthLabels[] = $r['ym'];
    $monthCounts[] = (int)$r['cnt'];
}

/* ---------- Top 5 ที่พักยอดวิวสูงสุด ---------- */
$topViewed = mysqli_query($con, "SELECT d.id, d.name, d.view_count
    FROM dormitories d WHERE d.status='อนุมัติแล้ว'
    ORDER BY d.view_count DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - DormHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<link rel="stylesheet" href="style.css">
<style>
    body{background:#f4f7f9;}
    .dash-wrap{max-width:1200px;margin:30px auto;padding:0 15px;}
    .stat-card{background:#fff;border-radius:12px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,.06);height:100%;}
    .stat-num{font-size:28px;font-weight:700;color:#17324A;}
    .stat-label{color:#6b7c85;font-size:13px;}
    .chart-card{background:#fff;border-radius:12px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,.06);margin-top:20px;}
    .badge-pill{border-radius:20px;padding:6px 14px;}
    @media (max-width:768px){ .stat-num{font-size:22px;} }
</style>
</head>
<body>
<header class="header">
    <div class="header-left">
        <a href="admin-Status.php"><img src="รูป/Dormhub-no.png" alt="Dormhub" class="logo"></a>
        <span class="site-name">Dormhub Admin</span>
    </div>

    <button class="nav-toggle" id="navToggle" aria-label="เมนู" onclick="toggleMobileNav()">
        <span></span><span></span><span></span>
    </button>
    <div class="nav-overlay" id="navOverlay" onclick="closeMobileNav()"></div>

    <div class="header-right" id="headerRight">
        <nav class="menu">
            <a href="admin-Status.php">ตรวจสอบที่พัก</a>
            <a href="admin-dashboard.php" class="active">แดชบอร์ด</a>
            <a href="viewhomepage.php">ออกจากระบบ</a>
        </nav>
    </div>
</header>

<div class="dash-wrap">
    <h3 class="mb-3">ภาพรวมระบบ DormHub</h3>

    <div class="row g-3">
        <div class="col-6 col-md-2">
            <div class="stat-card"><div class="stat-num"><?= $total ?></div><div class="stat-label">ที่พักทั้งหมด</div></div>
        </div>
        <div class="col-6 col-md-2">
            <div class="stat-card"><div class="stat-num text-warning"><?= $pending ?></div><div class="stat-label">รอการอนุมัติ</div></div>
        </div>
        <div class="col-6 col-md-2">
            <div class="stat-card"><div class="stat-num text-success"><?= $approved ?></div><div class="stat-label">อนุมัติแล้ว</div></div>
        </div>
        <div class="col-6 col-md-2">
            <div class="stat-card"><div class="stat-num text-danger"><?= $rejected ?></div><div class="stat-label">ถูกปฏิเสธ</div></div>
        </div>
        <div class="col-6 col-md-2">
            <div class="stat-card"><div class="stat-num"><?= $totalUsers ?></div><div class="stat-label">เจ้าของที่พักทั้งหมด</div></div>
        </div>
        <div class="col-6 col-md-2">
            <div class="stat-card"><div class="stat-num"><?= number_format($totalViews) ?></div><div class="stat-label">ยอดเข้าชมรวม</div></div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-6">
            <div class="chart-card">
                <h6>สัดส่วนที่พักตามประเภท</h6>
                <canvas id="typeChart" height="220"></canvas>
            </div>
        </div>
        <div class="col-md-6">
            <div class="chart-card">
                <h6>จำนวนที่พักที่เพิ่มเข้ามาต่อเดือน (6 เดือนล่าสุด)</h6>
                <canvas id="monthChart" height="220"></canvas>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="chart-card">
                <h6>ที่พักยอดเข้าชมสูงสุด (Top 5)</h6>
                <table class="table table-sm mt-2">
                    <thead><tr><th>ชื่อที่พัก</th><th class="text-center">ยอดเข้าชม</th></tr></thead>
                    <tbody>
                    <?php if (mysqli_num_rows($topViewed) === 0): ?>
                        <tr><td colspan="2" class="text-center text-muted">ยังไม่มีข้อมูล</td></tr>
                    <?php else: while ($t = mysqli_fetch_assoc($topViewed)): ?>
                        <tr>
                            <td><?= htmlspecialchars($t['name']) ?></td>
                            <td class="text-center"><?= number_format($t['view_count']) ?></td>
                        </tr>
                    <?php endwhile; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
new Chart(document.getElementById('typeChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($typeLabels, JSON_UNESCAPED_UNICODE) ?>,
        datasets: [{
            data: <?= json_encode($typeCounts) ?>,
            backgroundColor: ['#4FB8C4', '#E8A33D', '#17324A', '#B4543F']
        }]
    },
    options: { plugins: { legend: { position: 'bottom' } } }
});

new Chart(document.getElementById('monthChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($monthLabels) ?>,
        datasets: [{
            label: 'ที่พักใหม่',
            data: <?= json_encode($monthCounts) ?>,
            backgroundColor: '#4FB8C4'
        }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
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
