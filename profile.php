<?php if(isset($_SESSION['user'])): ?>
<?php
    $username = $_SESSION['user']['username'];
    $photo = !empty($_SESSION['user']['photo'])
        ? $_SESSION['user']['photo']
        : 'คน.png';
    // ถ้าเป็นไฟล์ custom upload ให้ใช้ path เต็ม ถ้าเป็นแกลเลอรี่ให้เพิ่ม รูป/ นำหน้า
    $photoSrc = (strpos($photo, '/') !== false) ? $photo : 'รูป/' . $photo;
?>
<div class="profile">
    <img src="<?= $photoSrc ?>" class="profile-avatar" onclick="toggleProfileMenu()">

    <div class="profile-menu" id="profile-menu">
        <div class="profile-top">
            <img src="<?= $photoSrc ?>" class="profile-big">
            <div class="profile-name"><?= htmlspecialchars($username) ?></div>
            <a href="profile-setting.php" class="setting-btn">⚙️</a>
        </div>
        
        <div class="profile-title">เปลี่ยนรูปโปรไฟล์</div>

        <div class="avatar-list">
            <img src="รูป/เป็ดแบร่.png" onclick="changeAvatar('เป็ดแบร่.png')">
            <img src="รูป/เป็ดง่วง.png" onclick="changeAvatar('เป็ดง่วง.png')">
            <img src="รูป/เป็ดขำ.png" onclick="changeAvatar('เป็ดขำ.png')">
            <img src="รูป/เป็ดรัก.png" onclick="changeAvatar('เป็ดรัก.png')">
            <img src="รูป/เป็ดโกด.png" onclick="changeAvatar('เป็ดโกด.png')">
            <img src="รูป/เป็ดตื่น.png" onclick="changeAvatar('เป็ดตื่น.png')">
        </div>

        <a href="logout.php" class="logout-btn">ออกจากระบบ</a>
    </div>
</div>
<?php endif; ?>
