<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มหอพัก - Admin DormHub</title>
    
   <?php
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'owner') {
    header("Location: viewhomepage.php");
    exit();
    }
    ?>
    
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

            #gallery-thumbs {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)) !important;
                gap: 10px !important;
            }

            #gallery-thumbs img {
                height: 100px !important;
            }

            #gallery-thumbs label {
                font-size: 12px;
            }

            input[type="checkbox"] {
                width: 18px;
                height: 18px;
                cursor: pointer;
            }

            .row {
                flex-wrap: wrap;
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

            #gallery-thumbs {
                grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)) !important;
                gap: 8px !important;
            }

            #gallery-thumbs img {
                height: 80px !important;
            }
        }
    </style>
</head>
<body>
    <?php 
    include "connect.php";
    $id=$_GET["id"];
    $path=$_GET["path"];

    $sql="SELECT d.id, d.name, d.price, d.price_unit, d.water_rate, d.electricity_rate, d.contact_link, d.status, d.user_id, 
	    l.address, l.area, l.latitude, l.longitude, 
	    t.type_name AS type, 
	    (SELECT image_path FROM dorm_images WHERE dorm_id = d.id AND is_main = 1 LIMIT 1) AS image, 
	    (SELECT GROUP_CONCAT(facility_name SEPARATOR ',') FROM dorm_facilities WHERE dorm_id = d.id) AS facilities,
	    (SELECT GROUP_CONCAT(image_path SEPARATOR ',') FROM dorm_images WHERE dorm_id = d.id AND is_main = 0) AS gallery 
	    FROM dormitories d 
	    LEFT JOIN dorm_locations l ON d.id = l.dorm_id 
	    LEFT JOIN dorm_types t ON d.type_id = t.id 
	    WHERE d.id=".$id;
    $result=mysqli_query($con,$sql); //ฟังก์ชั่นส่งคำสั่ง sql ไปที่ฐานข้อมูล
    $row=mysqli_fetch_array($result); //ดึงข้อมูลจากตัวแปล array result ทีละ record
    ?>
    <div class="admin-container">
        <h1 style="color: #333; margin-bottom: 30px;">📝 แก้ไขข้อมูลหอพัก</h1>
        
        <div style="text-align: right; margin-bottom: 20px;">
            <a href="Place-Me.php" style="color: #0011ff; text-decoration: none; font-weight: 600;">📝กลับสู่หน้าหอพักของฉัน</a>
        

        </div>
        
        <div id="messageBox"></div>
        
        <form action="update.php" method="POST" enctype="multipart/form-data">
            <!-- ข้อมูลพื้นฐาน -->
            <div class="form-section">
                <h3>📋 ข้อมูลพื้นฐาน</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>ชื่อหอพัก *</label>
                        <input type="text" id="name" name="name" value="<?php echo $row["name"]; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>ประเภท *</label>
                        <select id="type" name="type" required>
                            <option value="1" <?= ($row["type"] == 1 ? "selected" : "") ?>>หอพัก</option>
                            <option value="2" <?= ($row["type"] == 2 ? "selected" : "") ?>>อพาร์ทเม้นท์</option>
                            <option value="3" <?= ($row["type"] == 3 ? "selected" : "") ?>>คอนโด</option>
                            <option value="4" <?= ($row["type"] == 4 ? "selected" : "") ?>>บ้านพัก</option>

                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>ที่อยู่ *</label>
                    <textarea id="address" name="address" required placeholder="เช่น 123 ถ. พระราม 4 แขวง สีลม เขต บางรัก กรุงเทพฯ"><?php echo $row["address"]; ?></textarea>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>พื้นที่ใกล้เคียง *</label>
                        <input type="text" id="area" name="area" value="<?php echo $row["area"]; ?>" required placeholder="เช่น ราชภัฎเพชรบุรี, MBK, สยาม">
                    </div>

                    <div class="form-group">
                        <label>คิดราคาแบบ *</label>
                        <?php $priceUnit = isset($row['price_unit']) && $row['price_unit'] === 'วัน' ? 'วัน' : 'เดือน'; ?>
                        <select id="price_unit" name="price_unit" required onchange="togglePriceUnit()">
                            <option value="เดือน" <?= ($priceUnit === 'เดือน' ? "selected" : "") ?>>รายเดือน (หอพัก/อพาร์ทเม้นท์)</option>
                            <option value="วัน" <?= ($priceUnit === 'วัน' ? "selected" : "") ?>>รายวัน/รายคืน (ที่พักระยะสั้น)</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label id="price-label"><?= $priceUnit === 'วัน' ? 'ราคา/คืน (บาท) *' : 'ราคา/เดือน (บาท) *' ?></label>
                        <input type="number" id="price" name="price" value="<?php echo $row["price"]; ?>" required min="0">
                    </div>
                </div>
            </div>

            <div class="form-section" id="utility-section">
                <h3>💡 ค่าน้ำ-ค่าไฟ / หน่วย</h3>
                <p style="color: #666; margin-top: -10px; margin-bottom: 15px;">สำหรับที่พักรายเดือนเท่านั้น ที่พักรายวัน/รายคืนมักรวมค่าน้ำ-ไฟไว้ในราคาแล้ว</p>
                <div class="form-grid">
                    <div class="form-group">
                        <label>ค่าน้ำ (บาท/หน่วย)</label>
                        <input type="number" step="0.01" min="0" id="water_rate" name="water_rate" value="<?php echo isset($row['water_rate']) ? $row['water_rate'] : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>ค่าไฟ (บาท/หน่วย)</label>
                        <input type="number" step="0.01" min="0" id="electricity_rate" name="electricity_rate" value="<?php echo isset($row['electricity_rate']) ? $row['electricity_rate'] : ''; ?>">
                    </div>
                </div>
            </div>
            
            <!-- สิ่งอำนวยความสะดวก -->
            <div class="form-section">
                <h3>🏠 สิ่งอำนวยความสะดวก</h3>
                <div class="form-group">
                    <label>รายการสิ่งอำนวยความสะดวก *</label>
                    <textarea id="facilities" name="facilities" required placeholder="เช่น WiFi ฟรี, แอร์, ร้อนเย็น, ที่จอดรถ, CCTV"><?php echo $row["facilities"]; ?></textarea>
                </div>
            </div>

            <!-- ลิ้งติดต่อ -->
            <div class="form-section">
                <h3>📱 ลิ้งติดต่อ</h3>
                <div class="form-group">
                    <label>ลิ้งติดต่อ *</label>
                    <input type="url" id="contact_link" name="contact_link" value="<?php echo isset($row["contact_link"]) ? htmlspecialchars($row["contact_link"]) : ''; ?>" required placeholder="เช่น https://line.me/ti/p/... หรือ https://wa.me/...">
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
                        <input type="number" id="latitude" name="latitude" step="0.00000001" value="<?php echo $row["latitude"]; ?>" required>
                    </div>
                    <div class="coord-box">
                        <div class="coord-label">Longitude (ลองจิจูด)</div>
                        <input type="number" id="longitude" name="longitude" step="0.00000001" value="<?php echo $row["longitude"]; ?>" required>
                    </div>
                </div>
            </div>
            
            <!-- รูปภาพ: แก้ไขภาพหลักและแกลเลอรี -->
            <div class="form-section">
                <h3>🖼️ รูปภาพ</h3>
                <div class="form-group">
                    <label>เปลี่ยนรูปโชว์บนการ์ด (Main image)</label>
                    <input class="form-control" type="file" name="main_image" accept="image/*">
                </div>

                <div class="form-group">
                    <label>เพิ่มรูปแกลเลอรี (สามารถเลือกหลายไฟล์ได้)</label>
                    <input class="form-control" type="file" name="images[]" multiple accept="image/*">
                </div>

                <div style="margin-top:12px;">
                    <strong>รูปปัจจุบัน:</strong>
                </div>
                <div class="row" style="gap:12px; margin-top:8px; align-items:center;">
                    <div>
                        <img src="<?php echo htmlspecialchars($row["image"]); ?>" class="img-thumbnail" width="120" alt="main">
                    </div>
                </div>

                <?php
                // แสดงแกลเลอรีเก่า (ถ้ามี) เป็น thumbnail พร้อม checkbox ลบ
                $existingImages = [];
                if (!empty($row['images'])) {
                    $decoded = json_decode($row['images'], true);
                    if (is_array($decoded)) $existingImages = $decoded;
                } elseif (!empty($row['gallery'])) {
                    $decoded = array_filter(array_map('trim', explode(',', $row['gallery'])));
                    if (!empty($decoded)) $existingImages = $decoded;
                }
                if (!empty($existingImages)):
                ?>
                <div style="margin-top:16px;">
                    <strong>แกลเลอรีที่มีอยู่ (ติ๊กเพื่อเลือกลบ):</strong>
                    <div style="margin-top:8px;">
                        <label style="font-weight:500;">
                            <input type="checkbox" id="delete_all" name="delete_all_images" value="1"> ลบทุกรูปในแกลเลอรี
                        </label>
                    </div>
                    <div id="gallery-thumbs" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 15px; margin-top: 12px;">
                        <?php foreach ($existingImages as $img): ?>
                            <label style="display: block; text-align: center; cursor: pointer;">
                                <img src="<?php echo htmlspecialchars($img); ?>" style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px; border: 2px solid #ddd; display: block; margin-bottom: 8px;">
                                <div style="font-size: 13px;">
                                    <input type="checkbox" name="delete_images[]" value="<?php echo htmlspecialchars($img); ?>"> ลบ
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const deleteAll = document.getElementById('delete_all');
                        if (deleteAll) {
                            deleteAll.addEventListener('change', function() {
                                const boxes = document.querySelectorAll('#gallery-thumbs input[type=checkbox]');
                                boxes.forEach(cb => { cb.checked = deleteAll.checked; });
                            });
                        }
                    });
                </script>
                <?php endif; ?>

                <input type="hidden" id="id" name="id" value="<?php echo $row["id"];?>">
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
</body>
</html>
