<?php

// โหลดไฟล์ตั้งค่าการเชื่อมต่อฐานข้อมูล (ครั้งเดียว)
include_once 'connect.php';


$filterSql = '';
$conditions = ["d.status = 'อนุมัติแล้ว'"];

if (isset($filterType) && is_numeric($filterType)) {
    $conditions[] = "d.type_id = " . intval($filterType);
}

$whereClause = '';
if (!empty($conditions)) {
    $whereClause = " WHERE " . implode(" AND ", $conditions);
}

$query = "SELECT d.id, d.name, d.price, d.price_unit, d.status, d.user_id, d.water_rate, d.electricity_rate, d.contact_link, d.view_count,
       l.address, l.area, l.latitude, l.longitude, 
       t.type_name AS type, 
       (SELECT image_path FROM dorm_images WHERE dorm_id = d.id AND is_main = 1 LIMIT 1) AS image, 
       (SELECT GROUP_CONCAT(facility_name SEPARATOR ',') FROM dorm_facilities WHERE dorm_id = d.id) AS facilities 
FROM dormitories d
LEFT JOIN dorm_locations l ON d.id = l.dorm_id 
LEFT JOIN dorm_types t ON d.type_id = t.id 
$whereClause 
ORDER BY d.id DESC";
$result = mysqli_query($con, $query);

// เตรียมอาร์เรย์เก็บข้อมูลหอพัก
$dormitories = [];

// ลูปเก็บข้อมูลแต่ละแถวลงในอาร์เรย์
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // แปลง type จากตัวเลขเป็นชื่อประเภท
        $row['type'] = getTypeName($row['type']);
        $dormitories[] = $row;
    }
}

// นับจำนวนหอพัก
$totalDorms = count($dormitories);

// สร้าง ID สำหรับ carousel นี้ (แยกตาม type หรือ all)
$carouselId = isset($filterType) && is_numeric($filterType) ? 'carousel_type' . intval($filterType) : 'carousel_all';
?>

<!-- ===== CSS สำหรับ Carousel ===== -->
<style>
   

    .dorm-carousel-container {
        position: relative;
        width: 100%;
        max-width: 1200px;
        overflow: hidden;
        padding: 20px 70px; /* จองที่สำหรับปุ่ม */
        margin: 0 auto;
    }

    .dorm-carousel-wrapper {
        display: grid;
        grid-auto-flow: column;
        grid-auto-columns: calc((100% - 80px) / 5); /* ขนาดการ์ดเท่าเมื่อมี 5 การ์ด */
        gap: 20px;
        overflow-x: auto; /* อนุญาตการเลื่อนแนวนอนด้วยนิ้ว/เมาส์ */
        scroll-behavior: smooth;
        padding: 0 10px;
        scrollbar-width: none;
        scroll-snap-type: x mandatory;
        touch-action: pan-y pan-x;
    }


    .dorm-carousel-wrapper::-webkit-scrollbar {
        display: none; /* ซ่อน scrollbar Chrome */
    }
    .dorm-carousel-item {
        width: 100%;
        scroll-snap-align: start;
    }


    @media (max-width: 1400px) {
    .dorm-carousel-wrapper {
        grid-auto-columns: calc((100% - 60px) / 4);
    }
}

@media (max-width: 1024px) {
    .dorm-carousel-wrapper {
        grid-auto-columns: calc((100% - 40px) / 3);
    }
}

@media (max-width: 768px) {
    .dorm-carousel-wrapper {
        grid-auto-columns: calc((100% - 20px) / 2);
    }
}

@media (max-width: 480px) {
    .dorm-carousel-wrapper {
        grid-auto-columns: 100%;
    }
}


    .carousel-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: #4FB8C4;
        color: #fff;
        border: none;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        transition: all 0.3s ease;
    }

    .carousel-btn:hover {
        background: #3A96A1;
        transform: translateY(-50%) scale(1.1);
    }

    .carousel-btn:disabled {
        background: #ccc;
        color: #666;
        opacity: 0.6;
        cursor: not-allowed;
        box-shadow: none;
    }

    .carousel-btn-prev {
        left: 10px;
    }

    .carousel-btn-next {
        right: 10px;
    }

    .carousel-counter {
        text-align: center;
        margin-top: 15px;
        font-size: 14px;
        color: #666;
    }

    /* Mobile Responsive Carousel */
    @media (max-width: 768px) {
        .dorm-carousel-wrapper {
            grid-auto-columns: calc((100% - 20px) / 2) !important;
            gap: 15px !important;
            padding: 0 5px;
        }

        .carousel-btn {
            width: 40px;
            height: 40px;
            font-size: 18px;
        }

        .carousel-btn-prev {
            left: 5px;
        }

        .carousel-btn-next {
            right: 5px;
        }
    }

    @media (max-width: 480px) {
        .dorm-carousel-container {
            padding: 12px 12px;
        }

        .dorm-carousel-wrapper {
            grid-auto-columns: calc((100% - 16px) / 2) !important; /* ลดไม่ให้ใหญ่เกิน */
            gap: 10px !important;
            padding: 0 5px;
        }

        .dorm-carousel-item {
            min-width: 160px;
        }

        .carousel-btn {
            width: 36px;
            height: 36px;
            font-size: 16px;
        }

        .carousel-btn-prev {
            left: 5px;
            transform: translate(-40%, -50%);
        }

        .carousel-btn-next {
            right: 5px;
            transform: translate(40%, -50%);
        }

        .carousel-counter {
            font-size: 12px;
            margin-top: 10px;
        }
    }
</style>

<div class="dorm-results">
    
    <!-- ===============================
         ตรวจสอบว่ามีข้อมูลหอพักหรือไม่
         =============================== -->
    <?php if ($totalDorms > 0): ?>
        
        <!-- Carousel Container -->
        <div class="dorm-carousel-container" data-carousel-id="<?php echo $carouselId; ?>">
            
            <!-- ปุ่ม Previous -->
            <button class="carousel-btn carousel-btn-prev" data-carousel-id="<?php echo $carouselId; ?>" data-direction="-1">
                ❮
            </button>
            
            <!-- Carousel Wrapper - แสดงการ์ดแบบเลื่อนข้างๆ -->
            <div class="dorm-carousel-wrapper" data-carousel-id="<?php echo $carouselId; ?>">
                
                <!-- ลูปแสดงแต่ละการ์ดหอพัก -->
                <?php foreach ($dormitories as $index => $dorm): ?>
                    <div class="dorm-carousel-item">
                        <div class="dorm-card">
                            
                            <!-- ===== ส่วนรูปภาพ ===== -->
                            <!-- แสดงรูปภาพหอพัก หากไม่มีให้ใช้รูปภาพเริ่มต้น (Dormhub-no.jpg) -->
                            <img src="<?php echo htmlspecialchars($dorm['image'] ?: 'Dormhub-no.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($dorm['name']); ?>" 
                                 class="dorm-card-image">
                            
                            <!-- ===== ส่วนเนื้อหา ===== -->
                            <div class="dorm-card-content">
                                
                                <!-- ชื่อหอพัก -->
                                <div class="dorm-card-name">
                                    <?php echo htmlspecialchars($dorm['name']); ?>
                                </div>
                                
                                <!-- ส่วนข้อมูลหอพัก -->
                                <div class="dorm-card-info">
                                    
                                    <!-- ราคา -->
                                    <div class="dorm-card-price">
                                        ฿<?php echo number_format($dorm['price']); ?>/<?php echo (isset($dorm['price_unit']) && $dorm['price_unit'] === 'วัน') ? 'คืน' : 'เดือน'; ?>
                                    </div>
                                    
                                    <!-- ประเภท -->
                                    <div>
                                        🏠 <?php echo htmlspecialchars($dorm['type']); ?>
                                    </div>
                                    
                                    <!-- พื้นที่ที่ตั้ง -->
                                    <div>
                                        📍 <?php echo htmlspecialchars($dorm['area']); ?>
                                    </div>
                                    
                                    <!-- ที่อยู่ -->
                                    <div style="font-size: 13px; color: #999;">
                                        <?php echo htmlspecialchars($dorm['address']); ?>
                                    </div>
                                    <!-- ยอดเข้าชม -->
                                    <div style="font-size: 12.5px; color: #999; margin-top:4px;">
                                        👁 <?php echo number_format($dorm['view_count']); ?> ครั้ง
                                    </div>
                                </div>
                                
                                <!-- ===== ปุ่ม ===== -->
                                <button class="dorm-card-button" onclick="viewDetails(<?php echo $dorm['id']; ?>)">
                                    ดูรายละเอียด
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- ปุ่ม Next -->
            <button class="carousel-btn carousel-btn-next" data-carousel-id="<?php echo $carouselId; ?>" data-direction="1">
                ❯
            </button>
        </div>
        
        <!-- แสดงจำนวน Card -->
        <div class="carousel-counter">
            รวม <?php echo $totalDorms; ?> หอพัก
        </div>
    
    <!-- ถ้าไม่มีข้อมูล -->
    <?php else: ?>
        <div class="no-results">
            <p>ไม่พบข้อมูลหอพัก</p>
        </div>
    <?php endif; ?>
</div>

<!-- ===== JavaScript สำหรับ Carousel ===== -->
<script>
    if (!window.dormCarouselInitialized) {
        window.dormCarouselInitialized = true;

        function getCardScrollAmount(wrapper) {
            const item = wrapper.querySelector('.dorm-carousel-item');
            if (!item) return 300;
            const style = window.getComputedStyle(wrapper);
            const gap = parseFloat(style.columnGap || style.gridColumnGap || '20');
            return item.offsetWidth + (isNaN(gap) ? 20 : gap);
        }

        function updateButtonStates(wrapper) {
            const container = wrapper.closest('.dorm-carousel-container');
            if (!container) return;
            const prevBtn = container.querySelector('.carousel-btn-prev');
            const nextBtn = container.querySelector('.carousel-btn-next');
            const maxScroll = wrapper.scrollWidth - wrapper.clientWidth;
            if (prevBtn) prevBtn.disabled = wrapper.scrollLeft <= 0;
            if (nextBtn) nextBtn.disabled = wrapper.scrollLeft >= maxScroll - 10;
        }

        function scrollCarousel(wrapper, direction) {
            const currentScroll = wrapper.scrollLeft;
            const cardWidth = getCardScrollAmount(wrapper);
            const newScroll = currentScroll + (direction * cardWidth);
            wrapper.scrollTo({ left: newScroll, behavior: 'smooth' });
            setTimeout(() => updateButtonStates(wrapper), 300);
        }

        function initCarousels() {
            const containers = document.querySelectorAll('.dorm-carousel-container');
            containers.forEach(container => {
                const wrapper = container.querySelector('.dorm-carousel-wrapper');
                if (!wrapper) return;

                const prevBtn = container.querySelector('.carousel-btn-prev');
                const nextBtn = container.querySelector('.carousel-btn-next');

                if (prevBtn) {
                    prevBtn.addEventListener('click', () => scrollCarousel(wrapper, -1));
                }
                if (nextBtn) {
                    nextBtn.addEventListener('click', () => scrollCarousel(wrapper, 1));
                }

                wrapper.addEventListener('wheel', e => {
                    // ให้เลื่อนหน้าแนวตั้งได้ตามปกติ ถ้าไม่ได้เลื่อนในแกน x มากกว่าจริง
                    if (Math.abs(e.deltaX) > Math.abs(e.deltaY)) {
                        e.preventDefault();
                        wrapper.scrollBy({ left: e.deltaX, behavior: 'smooth' });
                    }
                }, { passive: false });

                // ไม่บล็อก touchmove ทั้งหมด เพื่อให้ scroll หน้าเว็บแนวตั้งทำงาน
                // แต่สามารถเลื่อน carousel แนวนอนด้วย swipe ได้ถ้าต้องการ
                wrapper.addEventListener('touchmove', e => {
                    // ยอมให้เบราว์เซอร์จัดการเอง (touch-action CSS จะช่วย)
                }, { passive: true });

                updateButtonStates(wrapper);
                window.addEventListener('resize', () => updateButtonStates(wrapper));
            });
        }

        window.addEventListener('DOMContentLoaded', initCarousels);
        window.addEventListener('load', initCarousels);
    }
</script>