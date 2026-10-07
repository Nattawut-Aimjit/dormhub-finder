<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: viewhomepage.php");
    exit();
}

include "connect.php";
$user_id = $_SESSION['user']['id'];

// make sure phone column exists (migration)
$colCheck = mysqli_query($con, "SHOW COLUMNS FROM users LIKE 'phone'");
if ($colCheck && mysqli_num_rows($colCheck) === 0) {
    mysqli_query($con, "ALTER TABLE users ADD phone VARCHAR(50) NULL");
}

// read current data
$stmt = mysqli_prepare($con, "SELECT username, email, photo, phone FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($res);

$username = htmlspecialchars($user['username'] ?? '');
$email = htmlspecialchars($user['email'] ?? '');
$phone = htmlspecialchars($user['phone'] ?? '');
$photo = !empty($user['photo']) ? $user['photo'] : 'คน.png';
// ถ้าเป็นไฟล์ custom upload ให้ใช้ path เต็ม ถ้าเป็นแกลเลอรี่ให้เพิ่ม รูป/ นำหน้า
$photoSrc = (strpos($photo, '/') !== false) ? $photo : 'รูป/' . $photo;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตั้งค่าผู้ใช้ - DormHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
    <style>
        .setting-container { max-width: 600px; margin: 40px auto; padding: 30px 40px; background:#ffffff; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.1); }
        .setting-section { margin-bottom: 40px; }
        .setting-section h3 { margin-bottom: 20px; color:#333; font-weight:600; border-bottom:2px solid #4FB8C4; padding-bottom:8px; }
        .form-group label { font-weight:500; color:#555; }
        .form-group input { width: 100%; }
        .form-control { border:1px solid #ccc; border-radius:6px; padding:10px; }
        .btn-primary { background:#4FB8C4; border:none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .btn-primary:active { background:#3A96A1; transform: scale(0.98); }
        .btn-warning { background:#ff9800; border:none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .btn-warning:active { background:#fb8c00; transform: scale(0.98); }
        .success-msg { background:#d4edda; color:#155724; padding:10px; border-radius:6px; margin-top:10px; }
        .error-msg { background:#f8d7da; color:#721c24; padding:10px; border-radius:6px; margin-top:10px; }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .setting-container {
                max-width: 100%;
                margin: 20px auto;
                padding: 20px;
            }

            .setting-section {
                margin-bottom: 30px;
            }

            .setting-section h3 {
                font-size: 18px;
                margin-bottom: 15px;
            }

            .form-group label {
                font-size: 14px;
                margin-bottom: 8px;
            }

            .form-control {
                padding: 12px;
                font-size: 16px;
            }

            .btn-primary, .btn-warning {
                width: 100%;
                padding: 12px;
                font-size: 16px;
                margin-bottom: 10px;
            }

            .avatar-list {
                grid-template-columns: repeat(3, 1fr) !important;
                max-width: 100% !important;
            }

            .avatar-list img {
                width: 70px !important;
                height: 70px !important;
            }
        }

        @media (max-width: 480px) {
            .setting-container {
                margin: 10px auto;
                padding: 15px;
            }

            .setting-section h3 {
                font-size: 16px;
            }

            .form-control {
                padding: 10px;
            }

            .btn-primary, .btn-warning {
                padding: 10px;
            }

            .avatar-list {
                grid-template-columns: repeat(2, 1fr) !important;
            }

            .avatar-list img {
                width: 60px !important;
                height: 60px !important;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <?php include "head.php"; ?>
    <div class="setting-container">
        <div class="setting-section">
            <h3>ข้อมูลสมาชิก</h3>
            <form id="profileForm">
                <div class="form-group">
                    <label>ชื่อผู้ใช้</label>
                    <input type="text" name="username" class="form-control" value="<?= $username ?>" required>
                </div>
                <div class="form-group">
                    <label>อีเมล</label>
                    <input type="email" name="email" class="form-control" value="<?= $email ?>">
                </div>
                <div class="form-group">
                    <label>เบอร์โทรศัพท์</label>
                    <input type="text" name="phone" class="form-control" value="<?= $phone ?>">
                </div>
                <button type="submit" class="btn btn-primary">บันทึก</button>
                <div id="profileMsg"></div>
            </form>
        </div>

        <div class="setting-section">
            <h3>เปลี่ยนรูปโปรไฟล์</h3>
            <div style="margin-bottom:20px; text-align:center;">
                <label style="display:block; margin-bottom:12px; font-weight:500;">รูปโปรไฟล์ปัจจุบัน</label>
                <img src="<?= $photoSrc ?>" style="width:120px; height:120px; border-radius:50%; object-fit:cover; border:3px solid #4FB8C4;">
            </div>
            <div style="margin-bottom:20px;">
                <label style="display:block; margin-bottom:8px; font-weight:500;">อัพโหลดรูปเอง</label>
                <input type="file" id="customPhoto" name="customPhoto" accept="image" style="padding:10px; border:1px solid #ccc; border-radius:6px; width:100%;">
                <button type="button" onclick="uploadCustomPhoto()" class="btn btn-primary" style="margin-top:10px;">อัพโหลด</button>
                <div id="uploadMsg"></div>
            </div>
            <div style="padding:10px 0; color:#999; font-size:12px;">หรือเลือกจากแกลเลอรี่ด้านล่าง</div>
            <div class="avatar-list" style="grid-template-columns: repeat(6, 1fr); max-width: 360px;">
                <?php
                $avatars = ['เป็ดแบร่.png','เป็ดง่วง.png','เป็ดขำ.png','เป็ดรัก.png','เป็ดโกด.png','เป็ดตื่น.png'];
                foreach ($avatars as $a) {
                    $cls = ($photo === $a) ? 'selected' : '';
                    echo "<img src=\"รูป/$a\" class=\"$cls\" onclick=\"changeAvatar('$a')\" style=\"width: 80px; height: 80px;\">";
                }
                ?>
            </div>
        </div>

        <div class="setting-section">
            <h3>เปลี่ยนรหัสผ่าน</h3>
            <form id="passwordForm">
                <div class="form-group">
                    <label>รหัสผ่านปัจจุบัน</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>รหัสผ่านใหม่</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>ยืนยันรหัสผ่านใหม่</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-warning">เปลี่ยนรหัสผ่าน</button>
                <div id="passwordMsg"></div>
            </form>
        </div>
    </div>

    <script>
    function uploadCustomPhoto() {
        const fileInput = document.getElementById('customPhoto');
        const file = fileInput.files[0];
        if (!file) {
            alert('กรุณาเลือกไฟล์ก่อน');
            return;
        }
        const formData = new FormData();
        formData.append('action', 'uploadPhoto');
        formData.append('photo', file);
        fetch('connect.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                const msg = document.getElementById('uploadMsg');
                if (data.success) {
                    msg.innerHTML = '<div class="success-msg">' + data.message + '</div>';
                    fileInput.value = '';
                    setTimeout(() => location.reload(), 1000);
                } else {
                    msg.innerHTML = '<div class="error-msg">' + (data.message || 'เกิดข้อผิดพลาด') + '</div>';
                }
            })
            .catch(err => alert('เกิดข้อผิดพลาด: ' + err));
    }
    
    function changeAvatar(avatarName) {
        fetch("connect.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: `action=updateAvatar&photo=${encodeURIComponent(avatarName)}`
        })
        .then(r=>r.json())
        .then(data=>{
            if (data.success) {
                location.reload();
            } else {
                alert('เกิดข้อผิดพลาด: '+data.message);
            }
        }).catch(err=>alert('เกิดข้อผิดพลาด: '+err));
    }

    document.getElementById('profileForm').addEventListener('submit', function(e){
        e.preventDefault();
        const form = e.target;
        const data = new URLSearchParams(new FormData(form));
        data.append('action','updateProfile');
        fetch('connect.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:data.toString()})
        .then(r=>r.json()).then(resp=>{
            const msg = document.getElementById('profileMsg');
            if (resp.success) {
                msg.innerHTML = '<div class="success-msg">'+resp.message+'</div>';
                setTimeout(()=>location.reload(),800);
            } else {
                msg.innerHTML = '<div class="error-msg">'+resp.message+'</div>';
            }
        });
    });

    document.getElementById('passwordForm').addEventListener('submit', function(e){
        e.preventDefault();
        const form = e.target;
        const data = new URLSearchParams(new FormData(form));
        data.append('action','changePassword');
        fetch('connect.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:data.toString()})
        .then(r=>r.json()).then(resp=>{
            const msg = document.getElementById('passwordMsg');
            if (resp.success) {
                msg.innerHTML = '<div class="success-msg">'+resp.message+'</div>';
                form.reset();
            } else {
                msg.innerHTML = '<div class="error-msg">'+resp.message+'</div>';
            }
        });
    });
    </script>

    <?php include "footer.php"; ?>
</body>
</html>