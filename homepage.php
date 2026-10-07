<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home-DormHub</title>

    <!--ติดตั้ง js css ในเครื่อง--->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<link rel="icon" href="Dormhub-no.jpg">
    <link rel="icon" href="Dormhub-no.jpg" sizes="32x32">
    <link rel="icon" href="Dormhub-no.jpg" sizes="16x16">
    
    <!-- Leaflet Map (ฟรี, ไม่ต้อง API key) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

    <style>
        /* Disable hover effects for menu - click only */
        .menu a {
            cursor: pointer;
        }
        
        .menu a:active,
        .menu a.active {
            color: #3A96A1;
            border-bottom: 2px solid #3A96A1;
            padding-bottom: 5px;
        }

        /* Button click effects */
        .add-btn:active {
            background-color: #3A96A1;
            transform: scale(0.98);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .text {
                font-size: 18px;
                padding-top: 15px;
                padding-bottom: 15px;
            }

            .header-center {
                width: 100%;
                margin: 10px 0;
                display: flex;
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
                padding: 0 12px;
                box-sizing: border-box;
            }

            .header-center input,
            .header-center .form-price,
            .header-center .search-btn {
                width: 100%;
                box-sizing: border-box;
            }

            .header-center input {
                padding: 8px;
                font-size: 14px;
            }

            .form-price {
                padding: 8px;
                font-size: 13px;
            }

            .search-btn {
                padding: 10px 12px;
                font-size: 15px;
            }
        }

        @media (max-width: 480px) {
            .bg {
                height: 400px;
            }

            .text {
                font-size: 16px;
                padding-top: 10px;
                padding-bottom: 10px;
            }

            .header-center input {
                padding: 10px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
<div>
    <?php
    
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'owner') {
    header("Location: viewhomepage.php");
    exit();
    }

    include "head.php";
    ?>
    <div class="bg" id="bg">
   
        <h1 class="text">รวมที่พัก หอพัก อพาร์ทเม้นท์ และอื่นๆ อีกมากมาย ครบจบที่นี่ที่เดียว</h1>
        <center><div class="header-center">
            <div class="search-input-wrap">
                <input type="text" id="searchLocation" placeholder="ค้าหา อพาร์ทเม้นท์-หอพัก ใกล้สถานที่ที่ต้องการ!" autocomplete="off">
                <div id="searchSuggestions" class="search-suggest-box"></div>
            </div>
             <select class="form-price" name="price" id="priceFilter">
                    <option value="all">ทุกช่วงราคา</option>
                    <option value="0-1000">0-1,000</option>
                    <option value="1000-3000">1,000-3,000</option>
                    <option value="3000-5000">3,000-5,000</option>
                    <option value="5000-7000">5,000-7,000</option>
                    <option value="7000-10000">7,000-10,000</option>
                    <option value="10000-20000">10,000-20,000</option>
                    <option value="20000-50000">20,000-50,000</option>
            </select>
            <button class="search-btn" onclick="searchDormitory()">🔍ค้าหาที่พัก!</button>
        </div></center>
        <center><div id="searchLocationHint" class="search-location-hint" style="display:none;"></div></center>
        
        <!-- แมพ -->
        <div id="map"></div>
        
        <!-- Modal Results -->
        <div id="resultsModal" class="modal">
            <div class="modal-content" style="width: 90%; max-width: 1000px; max-height: 80vh; overflow-y: auto;">
                <span class="close" onclick="closeResultsModal()">&times;</span>
                <h2>ผลลัพธ์การค้นหา</h2>
                <div id="dormCards" class="dorm-cards-container">
                    <!-- Card ที่พบจะอยู่ที่นี่ -->
                </div>
            </div>
        </div>
        
        <!-- Detail Modal -->
        <div id="detailModal" class="modal">
            <div class="modal-content" style="width: 90%; max-width: 1000px; max-height: 90vh; overflow-y: auto;">
                <span class="close" onclick="closeDetailModal()">&times;</span>
                <div id="detailContent">
                    <!-- Filled dynamically -->
                </div>
            </div>
        </div>
        
    </div>

     <div class="bgn">
        <h1 class="text1">หอพัก และ อพาร์ทเม้นท์ทั้งหมด</h1>
        <?php
            // แสดงผลทั้งหมด (ไม่มีการกรองประเภท)
            if (isset($filterType)) unset($filterType);
            include "showCard.php";  
        ?>  
    </div>

    <div class="type1" id="type1">
    
         <h1 class="text">หอพัก</h1>
         <?php
            // แสดงเฉพาะประเภท: หอพัก (type=1)
            $filterType = 1;
            include "showCard.php";  
            unset($filterType);
        ?> 
    </div>
    <div class="type2" id="type2">
    
         <h1 class="text">อพาร์ทเม้นท์</h1>
         <?php
            // แสดงเฉพาะประเภท: อพาร์ทเม้นท์ (type=2)
            $filterType = 2;
            include "showCard.php";  
            unset($filterType);
        ?> 
    </div>
    <div class="type3" id="type3">
    
         <h1 class="text">คอนโด</h1>
         <?php
            // แสดงเฉพาะประเภท: คอนโด (type=3)
            $filterType = 3;
            include "showCard.php";   
            unset($filterType);
        ?> 
    </div>
    <div class="type4" id="type4">
        <h1 class="text">บ้านพัก</h1>
         <?php
            // แสดงเฉพาะประเภท: บ้านพัก (type=4)
            $filterType = 4;
            include "showCard.php";   
            unset($filterType);
        ?> 
    </div>
    <?php
        include "footer.php";
    ?>
   </div>
    
    
    <script src="js/search-dorm.js"></script>
    <script>
        // Flag to indicate this is an authenticated view (show contact button)
        window.isViewOnlyMode = false;
    </script>
    
</body>
</html>