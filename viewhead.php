<header class="header">
    <div class="header-left">
        <a href="#bg1"><img src="รูป/Dormhub-no.png" alt="Dormhub" class="logo"></a>
        <span class="site-name">Dormhub</span>
    </div>

    <button class="nav-toggle" id="navToggle" aria-label="เมนู" onclick="toggleMobileNav()">
        <span></span><span></span><span></span>
    </button>
    <div class="nav-overlay" id="navOverlay" onclick="closeMobileNav()"></div>

    <div class="header-right" id="headerRight">
        <nav class="menu">
            <a href="viewhomepage.php#type1">หอพัก</a>
            <a href="viewhomepage.php#type2">อพาร์ทเม้นท์</a>
            <a href="viewhomepage.php#type3">คอนโด</a>
            <a href="viewhomepage.php#type4">บ้านพัก</a>
            <a href="viewhomepage.php#footer">ติดต่อเรา</a>
            <a href="javascript:void(0)" onclick="openLoginModal()">เข้าสู่ระบบ</a>
        </nav>
        <button class="add-btn" onclick="openLoginModal()">เพิ่มที่พักฟรี!</button>
    </div>
</header>

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
