<!-- Login Modal -->
<div id="loginModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeLoginModal()">&times;</span>
        <h2>เข้าสู่ระบบ</h2>

        <form id="loginForm" onsubmit="handleLogin(event)">
            <div class="form-group">
                <label>ชื่อผู้ใช้</label>
                <input type="text" id="login-username" name="username" required>
                <span id="login-username-error" class="error-msg"></span>
            </div>

            <div class="form-group">
                <label>รหัสผ่าน</label>
                <input type="password" id="login-password" name="password" required>
                <span id="login-password-error" class="error-msg"></span>
               
                
            </div>
            <div align="right">
                 <a href="reset-password.php" style="color: #f44336; font-weight: 600;">ลืมรหัสผ่าน</a>
            </div>
           

            <button type="submit" class="modal-btn">เข้าสู่ระบบ</button>
        </form>

        <p class="form-text">
            ยังไม่มีบัญชี?
            <a href="javascript:void(0)" onclick="switchToRegister()">สมัครสมาชิก</a>

        </p>
        
    </div>
     
</div>

<!-- Register Modal -->
<div id="registerModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeRegisterModal()">&times;</span>
        <h2>สมัครสมาชิก</h2>

        <form id="registerForm" onsubmit="handleRegister(event)">
            <div class="form-group">
                <label>ชื่อผู้ใช้</label>
                <input type="text" id="reg-username" name="username" required onblur="checkUsernameAvailable()">
                <span id="reg-username-error" class="error-msg"></span>
            </div>

            <div class="form-group">
                <label>อีเมล</label>
                <input type="email" id="reg-email" name="email" required>
                <span id="reg-email-error" class="error-msg"></span>
            </div>

            <div class="form-group">
                <label>เบอร์โทรศัพท์</label>
                <input type="text" id="reg-phone" name="phone">
            </div>

            <div class="form-group">
                <label>รหัสผ่าน</label>
                <input type="password" id="reg-password" name="password" required>
                <span id="reg-password-error" class="error-msg"></span>
            </div>

            <div class="form-group">
                <label>ยืนยันรหัสผ่าน</label>
                <input type="password" id="reg-confirm" name="confirm_password" required>
                <span id="reg-confirm-error" class="error-msg"></span>
            </div>

            <button type="submit" class="modal-btn">สมัครสมาชิก</button>
        </form>

        <p class="form-text">
            มีบัญชีแล้ว?
            <a href="javascript:void(0)" onclick="switchToLogin()">เข้าสู่ระบบ</a>
        </p>
    </div>
</div>
<script>
// ฟังก์ชันเปิด Modal Login
function openLoginModal() {
    document.getElementById("loginModal").style.display = "block";
    document.getElementById("registerModal").style.display = "none";
    clearLoginErrors();
}

// ฟังก์ชันปิด Modal Login
function closeLoginModal() {
    document.getElementById("loginModal").style.display = "none";
    clearLoginErrors();
}

// ฟังก์ชันเปิด Modal Register
function openRegisterModal() {
    document.getElementById("registerModal").style.display = "block";
    document.getElementById("loginModal").style.display = "none";
    clearRegisterErrors();
}

// ฟังก์ชันปิด Modal Register
function closeRegisterModal() {
    document.getElementById("registerModal").style.display = "none";
    clearRegisterErrors();
}

// สลับไปหน้า Register
function switchToRegister() {
    closeLoginModal();
    openRegisterModal();
}

// สลับไปหน้า Login
function switchToLogin() {
    closeRegisterModal();
    openLoginModal();
}


// ฟังก์ชันล้างข้อผิดพลาด Login
function clearLoginErrors() {
    document.getElementById("login-username-error").textContent = "";
    document.getElementById("login-password-error").textContent = "";
}

// ฟังก์ชันล้างข้อผิดพลาด Register
function clearRegisterErrors() {
    document.getElementById("reg-username-error").textContent = "";
    document.getElementById("reg-email-error").textContent = "";
    document.getElementById("reg-password-error").textContent = "";
    document.getElementById("reg-confirm-error").textContent = "";
}

// ฟังก์ชันตรวจสอบชื่อผู้ใช้ซ้ำ
function checkUsernameAvailable() {
    const username = document.getElementById("reg-username").value.trim();
    
    if (!username) {
        document.getElementById("reg-username-error").textContent = "";
        return;
    }
    
    fetch("connect.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `action=checkUsername&username=${encodeURIComponent(username)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.exists) {
            document.getElementById("reg-username-error").textContent = "ชื่อผู้ใช้นี้ถูกใช้แล้ว";
        } else {
            document.getElementById("reg-username-error").textContent = "";
        }
    })
    .catch(error => {
        console.log("ข้อผิดพลาด: " + error);
    });
}

// ฟังก์ชันจัดการ Login
function handleLogin(event) {
    event.preventDefault();
    clearLoginErrors();
    
    const username = document.getElementById("login-username").value.trim();
    const password = document.getElementById("login-password").value;
    
    if (!username || !password) {
        if (!username) {
            document.getElementById("login-username-error").textContent = "กรุณากรอกชื่อผู้ใช้";
        }
        if (!password) {
            document.getElementById("login-password-error").textContent = "กรุณากรอกรหัสผ่าน";
        }
        return;
    }

    // ส่งข้อมูล AJAX ไป connect.php
    fetch("connect.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `action=login&username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {

    // เช็ค role ที่ส่งกลับมาจาก server
    if (data.role === "admin") {
        location.href = "admin-Status.php";
    } else {
        location.href = "homepage.php";
    }

}
         else {
            if (data.error === "username") {
                document.getElementById("login-username-error").textContent = "ชื่อผู้ใช้ไม่พบ";
            } else if (data.error === "password") {
                document.getElementById("login-password-error").textContent = "รหัสผ่านไม่ถูกต้อง";
            } else {
                alert(data.message);
            }
        }
    })
    .catch(error => {
        alert("เกิดข้อผิดพลาด: " + error);
    });
}

// ฟังก์ชันจัดการ Register
function handleRegister(event) {
    event.preventDefault();
    clearRegisterErrors();
    
    const username = document.getElementById("reg-username").value.trim();
    const email = document.getElementById("reg-email").value.trim();
    const phone = document.getElementById("reg-phone").value.trim();
    const password = document.getElementById("reg-password").value;
    const confirm = document.getElementById("reg-confirm").value;
    
    let hasError = false;
    
    if (!username) {
        document.getElementById("reg-username-error").textContent = "กรุณากรอกชื่อผู้ใช้";
        hasError = true;
    }
    
    if (!email) {
        document.getElementById("reg-email-error").textContent = "กรุณากรอกอีเมล";
        hasError = true;
    }
    
    // phone optional, no validation needed here
    
    if (!password) {
        document.getElementById("reg-password-error").textContent = "กรุณากรอกรหัสผ่าน";
        hasError = true;
    }
    
    
    if (password !== confirm) {
        document.getElementById("reg-confirm-error").textContent = "รหัสผ่านไม่ตรงกัน";
        hasError = true;
    }
    
    if (hasError) return;

    // ส่งข้อมูล AJAX ไป connect.php
    fetch("connect.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `action=register&username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}&phone=${encodeURIComponent(phone)}&password=${encodeURIComponent(password)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ");
            document.getElementById("registerForm").reset();
            switchToLogin();
        } else {
            if (data.error === "username") {
                document.getElementById("reg-username-error").textContent = "ชื่อผู้ใช้นี้ถูกใช้แล้ว";
            } else if (data.error === "email") {
                document.getElementById("reg-email-error").textContent = "อีเมลนี้ถูกใช้แล้ว";
            } else {
                alert(data.message);
            }
        }
    })
    .catch(error => {
        alert("เกิดข้อผิดพลาด: " + error);
    });
}

// ปิด modal เมื่อคลิกนอกจาก modal
window.onclick = function(event) {
    const loginModal = document.getElementById("loginModal");
    const registerModal = document.getElementById("registerModal");
    
    if (event.target === loginModal) {
        loginModal.style.display = "none";
    }
    if (event.target === registerModal) {
        registerModal.style.display = "none";
    }
}
</script>