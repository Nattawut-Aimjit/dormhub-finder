 <?php
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'owner') {
    header("Location: viewhomepage.php");
    exit();
    }
    ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มที่พัก - DormHub</title>
    
   
    
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    
    <style>
        body {
            background: #f5f5f5;
            padding: 20px;
        }
        
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section h3 {
            color: #333;
            font-weight: 600;
            margin-bottom: 15px;
            border-bottom: 2px solid #4FB8C4;
            padding-bottom: 10px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Sarabun', Arial, sans-serif;
            font-size: 14px;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        #map {
            width: 100%;
            height: 400px;
            border: 2px solid #4FB8C4;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .coordinates {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .coord-box {
            background: #f0f8ff;
            padding: 12px;
            border-radius: 6px;
            border-left: 4px solid #4FB8C4;
        }
        
        .coord-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .coord-value {
            font-size: 18px;
            font-weight: 600;
            color: #4FB8C4;
            font-family: monospace;
        }
        
        .submit-btn {
            background: #4FB8C4;
            color: #fff;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .submit-btn:hover {
            background: #3A96A1;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 227, 255, 0.4);
        }
        
        .success-msg {
            background: #4caf50;
            color: #fff;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .error-msg {
            background: #f44336;
            color: #fff;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .admin-container {
                padding: 15px;
                border-radius: 8px;
            }

            h1 {
                font-size: 24px;
                margin-bottom: 15px;
            }

            .form-section h3 {
                font-size: 18px;
                margin-bottom: 12px;
                padding-bottom: 8px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .form-group {
                margin-bottom: 12px;
            }

            .form-group label {
                font-size: 14px;
                margin-bottom: 4px;
            }

            .form-group input,
            .form-group textarea,
            .form-group select {
                padding: 12px;
                font-size: 16px;
            }

            .form-group textarea {
                min-height: 80px;
            }

            #map {
                height: 300px;
                margin-bottom: 12px;
            }

            .coordinates {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .coord-box {
                padding: 10px;
            }

            .submit-btn {
                width: 100%;
                padding: 14px;
                font-size: 16px;
            }

            a {
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            .admin-container {
                padding: 10px;
            }

            h1 {
                font-size: 20px;
                margin-bottom: 12px;
            }

            .form-section h3 {
                font-size: 16px;
                margin-bottom: 10px;
            }

            .form-group input,
            .form-group textarea,
            .form-group select {
                padding: 10px;
                font-size: 16px;
            }

            #map {
                height: 250px;
            }

            .coord-label {
                font-size: 11px;
            }

            .submit-btn {
                padding: 12px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <h1 style="color: #333; margin-bottom: 30px;">➕ เพิ่มที่พัก</h1>
        
        <div style="text-align: right; margin-bottom: 20px;">
            <a href="Place-Me.php" style="color: #0011ff; text-decoration: none; font-weight: 600;">📝กลับสู่หน้าที่พักของฉัน</a>
            
        </div>
        
        <div id="messageBox"></div>
        
        <form action="insert.php" method="POST" enctype="multipart/form-data">
            <!-- ข้อมูลพื้นฐาน -->
            <div class="form-section">
                <h3>📋 ข้อมูลพื้นฐาน</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>ชื่อที่พัก *</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>ประเภท *</label>
                        <select id="type" name="type" required>
                            <option value="">-- เลือกประเภท --</option>
                            <option value="1">หอพัก</option>
                            <option value="2">อพาร์ทเม้นท์</option>
                            <option value="3">คอนโด</option>
                            <option value="4">บ้านพัก</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>ที่อยู่ *</label>
                    <textarea id="address" name="address" required placeholder="เช่น 123 ถ. พระราม 4 แขวง สีลม เขต บางรัก กรุงเทพฯ"></textarea>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>พื้นที่ใกล้เคียง *</label>
                        <input type="text" id="area" name="area" required placeholder="เช่น ราชภัฎเพชรบุรี, MBK, สยาม">
                    </div>

                    <div class="form-group">
                        <label>คิดราคาแบบ *</label>
                        <select id="price_unit" name="price_unit" required onchange="togglePriceUnit()">
                            <option value="เดือน">รายเดือน (หอพัก/อพาร์ทเม้นท์)</option>
                            <option value="วัน">รายวัน/รายคืน (ที่พักระยะสั้น)</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label id="price-label">ราคา/เดือน (บาท) *</label>
                        <input type="number" id="price" name="price" required min="0">
                    </div>
                </div>
            </div>

            <!-- ค่าน้ำ-ค่าไฟ -->
            <div class="form-section" id="utility-section">
                <h3>💡 ค่าน้ำ-ค่าไฟ / หน่วย</h3>
                <p style="color: #666; margin-top: -10px; margin-bottom: 15px;">สำหรับที่พักรายเดือนเท่านั้น ที่พักรายวัน/รายคืนมักรวมค่าน้ำ-ไฟไว้ในราคาแล้ว</p>
                <div class="form-grid">
                    <div class="form-group">
                        <label>ค่าน้ำ (บาท/หน่วย)</label>
                        <input type="number" step="0.01" min="0" id="water_rate" name="water_rate" placeholder="เช่น 8.50">
                    </div>
                    <div class="form-group">
                        <label>ค่าไฟ (บาท/หน่วย)</label>
                        <input type="number" step="0.01" min="0" id="electricity_rate" name="electricity_rate" placeholder="เช่น 3.64">
                    </div>
                </div>
            </div>
            
            <!-- สิ่งอำนวยความสะดวก -->
            <div class="form-section">
                <h3>🏠 สิ่งอำนวยความสะดวก</h3>
                <div class="form-group">
                    <label>รายการสิ่งอำนวยความสะดวก *</label>
                    <textarea id="facilities" name="facilities" required placeholder="เช่น WiFi ฟรี, แอร์, ร้อนเย็น, ที่จอดรถ, CCTV"></textarea>
                </div>
            </div>

            <!-- ลิ้งติดต่อ -->
            <div class="form-section">
                <h3>📱 ลิ้งติดต่อ</h3>
                <div class="form-group">
                    <label>ลิ้งติดต่อ *</label>
                    <input type="url" id="contact_link" name="contact_link" required placeholder="เช่น https://line.me/ti/p/... หรือ https://wa.me/...">
                    <small style="color: #666; display: block; margin-top: 5px;">ตัวอย่าง: Line: https://line.me/ti/p/~LINE_ID | WhatsApp: https://wa.me/66812345678 | Email: mailto:contact@example.com</small>
                </div>
            </div>
            
            <!-- ระบุตำแหน่งบนแมพ -->
            <div class="form-section">
                <h3>📍 ระบุตำแหน่งบนแมพ</h3>
                <p style="color: #666; margin-bottom: 15px;">💡 คลิกบนแมพเพื่อเลือกตำแหน่ง หรือกรอกพิกัดด้วยตนเอง</p>
                
                <div id="map"></div>
                
                <div class="coordinates">
                    <div class="coord-box">
                        <div class="coord-label">Latitude (ละติจูด)</div>
                        <input type="number" id="latitude" name="latitude" step="0.00000001" required>
                    </div>
                    <div class="coord-box">
                        <div class="coord-label">Longitude (ลองจิจูด)</div>
                        <input type="number" id="longitude" name="longitude" step="0.00000001" required>
                    </div>
                </div>
            </div>
            
            <!-- รูปภาพ: รูปหลักและแกลเลอรี -->
            <div class="form-section">
                <h3>🖼️ รูปภาพ</h3>
                <div class="form-group">
                    <label>เลือกรูปโชว์บนการ์ด (Main image) *</label>
                    <input class="form-control" type="file" name="main_image" accept="image/*" required>
                </div>

                <div class="form-group">
                    <label>เพิ่มรูปแกลเลอรี (สามารถเลือกหลายไฟล์ได้)</label>
                    <input class="form-control" type="file" name="images[]" multiple accept="image/*">
                </div>
            </div>
            
            
            <!-- ปุ่มส่ง -->
            <div style="text-align: center;">
                <button type="submit" class="submit-btn" name="button">💾 บันทึกหอพัก</button>

            </div>
        </form>
    </div>
    
    <script>
        // สลับแสดง/ซ่อนค่าน้ำ-ค่าไฟ และปรับ label ราคาตามประเภทการคิดราคา
        function togglePriceUnit() {
            const unit = document.getElementById("price_unit").value;
            const utilitySection = document.getElementById("utility-section");
            const priceLabel = document.getElementById("price-label");
            const waterInput = document.getElementById("water_rate");
            const electricityInput = document.getElementById("electricity_rate");

            if (unit === "วัน") {
                utilitySection.style.display = "none";
                priceLabel.textContent = "ราคา/คืน (บาท) *";
                // ไม่บังคับกรอกและล้างค่าเมื่อเป็นรายวัน
                if (waterInput) waterInput.value = "";
                if (electricityInput) electricityInput.value = "";
            } else {
                utilitySection.style.display = "";
                priceLabel.textContent = "ราคา/เดือน (บาท) *";
            }
        }

        let map;
        let marker;
        
        // เริ่มต้นแมพ
        function initMap() {
            map = L.map('map').setView([13.1171, 99.9440], 13); // Phetchaburi center
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);
            
            // คลิกบนแมพเพื่อเลือกตำแหน่ง
            map.on('click', function(e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;
                
                document.getElementById("latitude").value = lat.toFixed(8);
                document.getElementById("longitude").value = lng.toFixed(8);
                
                // ล้าง marker เดิม
                if (marker) {
                    map.removeLayer(marker);
                }
                
                // เพิ่ม marker ใหม่
                marker = L.marker([lat, lng])
                    .bindPopup(`<strong>ตำแหน่งที่เลือก</strong><br>Lat: ${lat.toFixed(8)}<br>Lng: ${lng.toFixed(8)}`)
                    .addTo(map)
                    .openPopup();
            });
        }
        
       
        
        // เริ่มต้นแมพเมื่อโหลดหน้า
        document.addEventListener("DOMContentLoaded", function() {
            initMap();
            togglePriceUnit();
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

    
</body>
</html>
