<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รีเซ็ตรหัสผ่าน - DormHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@600;700&family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Sarabun', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #4FB8C4 0%, #17324A 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .reset-container {
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
        }
        
        .reset-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .reset-header h1 {
            color: #17324A;
            font-family: 'Kanit', sans-serif;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .reset-header p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #4FB8C4;
            box-shadow: 0 0 8px rgba(79, 184, 196, 0.2);
        }
        
        .submit-btn {
            width: 100%;
            padding: 12px;
            background: #4FB8C4;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .submit-btn:active {
            background: #17324A;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 184, 196, 0.4);
        }
        
        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ef5350;
        }
        
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #81c784;
        }
        
        .alert-info {
            background: #e3f2fd;
            color: #1565c0;
            border: 1px solid #64b5f6;
        }
        
        .back-link {
            text-align: center;
            margin-top: 15px;
        }
        
        .back-link a {
            color: #4FB8C4;
            text-decoration: none;
            font-weight: 600;
        }
        
        .timer {
            text-align: center;
            color: #666;
            font-size: 13px;
            margin-top: 10px;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .reset-container {
                max-width: 100%;
                margin: 20px;
                padding: 30px 20px;
                border-radius: 12px;
            }

            .reset-header h1 {
                font-size: 22px;
            }

            .reset-header p {
                font-size: 13px;
            }

            .form-group label {
                font-size: 14px;
            }

            .form-group input {
                padding: 12px;
                font-size: 16px;
            }

            .submit-btn {
                padding: 14px;
                font-size: 16px;
            }

            .alert, .back-link {
                font-size: 13px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 0;
            }

            .reset-container {
                margin: 0;
                padding: 20px 15px;
                border-radius: 0;
                box-shadow: none;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }

            .reset-header h1 {
                font-size: 20px;
            }

            .reset-header p {
                font-size: 12px;
            }

            .form-group {
                margin-bottom: 15px;
            }

            .form-group input {
                padding: 10px;
                font-size: 16px;
            }

            .submit-btn {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <h1>🔑 รีเซ็ตรหัสผ่าน</h1>
            <p>ตั้งรหัสผ่านใหม่ของคุณ</p>
        </div>
        
        <?php
        session_start();
        include 'connect.php';
        
        // รับค่า action ก่อน
        $step = $_POST['step'] ?? 'email';
        $email = $_POST['email'] ?? '';
        $otp = $_POST['otp'] ?? '';
        $new_password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $error = '';
        $message = '';
        $success = false;
        
        // Step 1: ส่ง OTP
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'email' && !empty($email)) {
            // ตรวจสอบอีเมลว่ามีในระบบหรือไม่
            $check = mysqli_prepare($con, "SELECT id FROM users WHERE email = ?");
            mysqli_stmt_bind_param($check, "s", $email);
            mysqli_stmt_execute($check);
            $result = mysqli_stmt_get_result($check);
            
            if (mysqli_num_rows($result) > 0) {
                // สร้าง OTP 6 ตัว
                $otp_code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
                $otp_expire = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                
                // บันทึก OTP ลงฐานข้อมูล
                $update_otp = mysqli_prepare($con, "UPDATE users SET reset_otp = ?, reset_otp_expire = ? WHERE email = ?");
                mysqli_stmt_bind_param($update_otp, "sss", $otp_code, $otp_expire, $email);
                
                if (mysqli_stmt_execute($update_otp)) {
                    // [TEST MODE] แสดง OTP บน UI แทนการส่งอีเมล
                    $message = "✅ OTP สำหรับ $email: <strong style='color: #f44336; font-size: 18px;'>$otp_code</strong><br/>(Test Mode - คัดลอกรหัสนี้ไปใส่ในขั้นตอนถัดไป)";
                    $step = 'otp';
                } else {
                    $error = '❌ เกิดข้อผิดพลาดในระบบ';
                }
            } else {
                $error = '❌ ไม่พบอีเมลนี้ในระบบ';
            }
        }
        
        // Step 2: ตรวจสอบ OTP
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'otp' && !empty($otp) && !empty($email)) {
            $check_otp = mysqli_prepare($con, "SELECT id, reset_otp, reset_otp_expire FROM users WHERE email = ?");
            mysqli_stmt_bind_param($check_otp, "s", $email);
            mysqli_stmt_execute($check_otp);
            $result = mysqli_stmt_get_result($check_otp);
            $row = mysqli_fetch_array($result);
            
            // Debug: แสดงค่าที่ดึงมา
            $db_otp = $row['reset_otp'] ?? '';
            $db_expire = $row['reset_otp_expire'] ?? '';
            $user_otp = trim($otp);
            
            // เช็ค OTP + เวลา
            if ($row && $user_otp === $db_otp && strtotime($db_expire) > time()) {
                $message = '✅ OTP ถูกต้อง กรุณาตั้งรหัสผ่านใหม่';
                $step = 'password';
            } else {
                // Debug info
                $debug_msg = "OTP ที่ส่ง: $user_otp | DB OTP: $db_otp | ตรง: " . ($user_otp === $db_otp ? 'Yes' : 'No');
                $error = '❌ OTP ไม่ถูกต้องหรือหมดอายุแล้ว<br><small style="color: #999;">(' . $debug_msg . ')</small>';
                $step = 'otp';
            }
        }
        
        // Step 3: เปลี่ยนรหัสผ่าน
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'password' && !empty($new_password) && !empty($email)) {
            if (strlen($new_password) < 6) {
                $error = '❌ รหัสผ่านต้องมีอย่างน้อย 6 ตัว';
                $step = 'password';
            } elseif ($new_password !== $confirm_password) {
                $error = '❌ รหัสผ่านและการยืนยันไม่ตรงกัน';
                $step = 'password';
            } else {
                // แฮชรหัสผ่านใหม่
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                
                // อัพเดต
                $update = mysqli_prepare($con, "UPDATE users SET password = ?, reset_otp = NULL, reset_otp_expire = NULL WHERE email = ?");
                mysqli_stmt_bind_param($update, "ss", $password_hash, $email);
                
                if (mysqli_stmt_execute($update)) {
                    $success = true;
                } else {
                    $error = '❌ เกิดข้อผิดพลาด: ' . mysqli_error($con);
                }
            }
        }
        ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">✅ รีเซ็ตรหัสผ่านสำเร็จ!</div>
            <p style="color: #666; text-align: center; margin-bottom: 20px;">คุณสามารถเข้าสู่ระบบด้วยรหัสผ่านใหม่แล้ว</p>
            <div class="back-link">
                <a href="viewhomepage.php">← กลับไปหน้าหลัก</a>
            </div>
        <?php elseif ($step === 'email'): ?>
            <!-- ขั้นที่ 1: กรอกอีเมล -->
            <form method="POST">
                <input type="hidden" name="step" value="email">
                <div class="form-group">
                    <label>📧 กรอกอีเมลของคุณ</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required placeholder="example@email.com">
                </div>
                <button type="submit" class="submit-btn">📤 ส่ง OTP ไปที่อีเมล</button>
            </form>
            <div class="back-link">
                <a href="viewhomepage.php">← กลับไปหน้าหลัก</a>
            </div>
        <?php elseif ($step === 'otp'): ?>
            <!-- ขั้นที่ 2: กรอก OTP -->
            <form method="POST">
                <input type="hidden" name="step" value="otp">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                <div class="form-group">
                    <label>🔐 กรอก OTP ที่ได้รับจากอีเมล</label>
                    <input type="text" name="otp" maxlength="6" placeholder="000000" required autofocus>
                    <div class="timer">OTP มีอายุ 10 นาที</div>
                </div>
                <button type="submit" class="submit-btn">✅ ยืนยัน OTP</button>
            </form>
            <div class="back-link">
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="step" value="email">
                    <button type="submit" style="background: none; border: none; color: #4FB8C4; cursor: pointer; font-weight: 600;">← ใส่อีเมลใหม่</button>
                </form>
            </div>
        <?php elseif ($step === 'password'): ?>
            <!-- ขั้นที่ 3: ตั้งรหัสผ่านใหม่ -->
            <form method="POST">
                <input type="hidden" name="step" value="password">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                <div class="form-group">
                    <label>🔑 รหัสผ่านใหม่</label>
                    <input type="password" name="password" minlength="6" required placeholder="อย่างน้อย 6 ตัว">
                </div>
                <div class="form-group">
                    <label>🔑 ยืนยันรหัสผ่าน</label>
                    <input type="password" name="confirm_password" minlength="6" required placeholder="พิมพ์รหัสผ่านอีกครั้ง">
                </div>
                <button type="submit" class="submit-btn">✅ รีเซ็ตรหัสผ่าน</button>
            </form>
            <div class="back-link">
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="step" value="email">
                    <button type="submit" style="background: none; border: none; color: #4FB8C4; cursor: pointer; font-weight: 600;">← ใส่อีเมลใหม่</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
