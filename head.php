<header class="header">
   <div class="header-left">
            <a href="#bg"><img src="รูป/Dormhub-no.png" alt="Dormhub" class="logo"></a>
            <span class="site-name">Dormhub</span>
        </div>


    <button class="nav-toggle" id="navToggle" aria-label="เมนู" onclick="toggleMobileNav()">
        <span></span><span></span><span></span>
    </button>
    <div class="nav-overlay" id="navOverlay" onclick="closeMobileNav()"></div>

    <div class="header-right" id="headerRight">
        <nav class="menu">
            <?php
            // ถ้าอยู่ในหน้าตั้งค่าผู้ใช้ ให้แสดงลิงก์พิเศษ
            $current = basename($_SERVER['PHP_SELF']); 
            if ($current === 'profile-setting.php') {
                echo '<a href="homepage.php">กลับสู่หน้าหลัก</a>';
                echo '<a href="#footer">ติดต่อเรา</a>';
            } else {
                echo '<a href="#type1">หอพัก</a>';
                echo '<a href="#type2">อพาร์ทเม้นท์</a>';
                echo '<a href="#type3">คอนโด</a>';
                echo '<a href="#type4" class="menu-mobile-break">บ้านพัก</a>';
                echo '<a href="#footer">ติดต่อเรา</a>';
            }
            ?>
        </nav>
        <?php 
            include "notifications.php";
            include "profile.php";
        ?>

        <button class="add-btn" onclick="location.href='Place-Me.php'">เพิ่มที่พักฟรี!</button>
    </div>

    <?php include "login-register.php"; ?> 
</header>


<script>
// Profile Menu Toggle
function toggleProfileMenu(){
    const menu = document.getElementById("profile-menu");
    if (menu) {
        menu.classList.toggle("show");
    }
}

// Close profile menu when clicking outside
document.addEventListener("click", function(e){
    const profile = document.querySelector(".profile");
    const menu = document.getElementById("profile-menu");
    if (profile && menu && !profile.contains(e.target)) {
        menu.classList.remove("show");
    }
});

// Change Avatar
function changeAvatar(avatarName) {
    fetch("connect.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `action=updateAvatar&photo=${encodeURIComponent(avatarName)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert("เกิดข้อผิดพลาด: " + data.message);
        }
    })
    .catch(error => {
        alert("เกิดข้อผิดพลาด: " + error);
    });
}
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
