<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home-DormHub</title>

    <!--ติดตั้ง js css ในเครื่อง--->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="style.css">
<link rel="icon" href="Dormhub-no.jpg">
    <link rel="icon" href="Dormhub-no.jpg" sizes="32x32">
    <link rel="icon" href="Dormhub-no.jpg" sizes="16x16">
    

    

</head>
<body>
    <?php
    session_start();
    include "viewhead.php";
    ?>

   
    <div class="bg1" id="bg1">
   
        <h1 class="text1">รวมที่พัก หอพัก อพาร์ทเม้นท์ และอื่นๆ อีกมากมาย ครบจบที่นี่ที่เดียว</h1>
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
   



    <!-- Detail Modal (เหมือนหน้า homepage) -->
    <div id="detailModal" class="modal">
        <div class="modal-content" style="width: 90%; max-width: 1000px; max-height: 90vh; overflow-y: auto;">
            <span class="close" onclick="closeDetailModal()">&times;</span>
            <div id="detailContent">
                <!-- Filled dynamically -->
            </div>
        </div>
    </div>

    <!-- Optional results modal -->
    <div id="resultsModal" class="modal">
        <div class="modal-content" style="width: 90%; max-width: 1000px; max-height: 80vh; overflow-y: auto;">
            <span class="close" onclick="closeResultsModal()">&times;</span>
            <h2>ผลลัพธ์การค้นหา</h2>
            <div id="dormCards" class="dorm-cards-container">
                <!-- Card ที่พบจะอยู่ที่นี่ -->
            </div>
        </div>
    </div>

   
    <?php include "login-register.php"; ?>

    <script src="js/search-dorm.js"></script>
    <script>
        // Flag to indicate this is a non-authenticated view (requires login)
        window.isViewOnlyMode = true;
    </script>

</body>
</html>