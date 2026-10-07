<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'owner') {
    header("Location: viewhomepage.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css"> 
     <!--ติดตั้ง js css ในเครื่อง--->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <title>Add-Place</title>
    <style>
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 15px 10px;
                overflow-x: auto;
            }

            .container p {
                font-size: 22px;
                margin: 15px 0;
            }

            .table {
                font-size: 12px;
            }

            .table th, .table td {
                padding: 8px 4px;
            }

            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .btn {
                padding: 6px 10px;
                font-size: 12px;
            }
        }

        @media (max-width: 480px) {
            body {
                margin: 0;
                padding: 0;
            }

            .container {
                padding: 10px 8px;
                max-width: 100%;
                overflow-x: auto;
            }

            .container p {
                font-size: 16px;
                margin: 10px 0;
            }

            .table {
                font-size: 10px;
                table-layout: auto;
                width: 100%;
            }

            .table th {
                font-size: 9px;
                padding: 6px 2px;
                white-space: nowrap;
            }

            .table td {
                padding: 6px 2px;
                word-break: break-word;
            }

            .btn {
                padding: 4px 6px;
                font-size: 10px;
            }
        }
    </style>
</head>
<body>

<header class="header">
   <div class="header-left">
            <a href="Place-Me.php"><img src="รูป/Dormhub-no.png" alt="Dormhub" class="logo"></a>
            <span class="site-name">Dormhub</span>
        </div>


    <button class="nav-toggle" id="navToggle" aria-label="เมนู" onclick="toggleMobileNav()">
        <span></span><span></span><span></span>
    </button>
    <div class="nav-overlay" id="navOverlay" onclick="closeMobileNav()"></div>

    <div class="header-right" id="headerRight">
        <nav class="menu">
            <a href="homepage.php">กลับสู่หน้าหลัก</a>
            <a href="#" data-bs-toggle="modal" data-bs-target="#stipulationModal"> ข้อกำหนด</a>
            <a href="#footer">ติดต่อเรา</a>
        </nav>
        <?php 
            include "profile.php";
        ?>
    </div>
</header>


    <div class="container">
      <p>รอการตรวจสอบ</p>
        <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead>
                <tr class="table-primary">
                    <td class="text-center">ชื่อหอพัก</td>
                    <td class="text-center">ที่อยู่</td>
                    <td class="text-center">พื้นที่ใกล้เคียง</td>
                    <td class="text-center">ราคา</td>
                    <td class="text-center">ประเภท</td>
                    <td class="text-center">สิ่งอำนวยความสะดวก</td>
                    <td class="text-center">รูปภาพ</td>
                    <td class="text-center">ละติจูด</td>
                    <td class="text-center">ลองจิจูด</td>
                    <td class="text-center">สถานะ</td>
                    
                    
                  
                </tr>
            </thead>
           
            <?php
           
            include "connect.php";
           
            $user_id = intval($_SESSION['user']['id']);
            $sql = "SELECT d.id, d.name, d.price, d.water_rate, d.electricity_rate, d.contact_link, d.status, d.user_id, d.reject_reason,
	            l.address, l.area, l.latitude, l.longitude, 
	            t.type_name AS type, 
	            (SELECT image_path FROM dorm_images WHERE dorm_id = d.id AND is_main = 1 LIMIT 1) AS image, 
	            (SELECT GROUP_CONCAT(facility_name SEPARATOR ',') FROM dorm_facilities WHERE dorm_id = d.id) AS facilities 
	            FROM dormitories d 
	            LEFT JOIN dorm_locations l ON d.id = l.dorm_id 
	            LEFT JOIN dorm_types t ON d.type_id = t.id 
	            WHERE d.user_id = ?";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            while($row=mysqli_fetch_array($result)) //ดึงข้อมูลออกจาก array $result ทีละ record
                {
                    if(empty($row["name"])) continue;
            ?>
                <tr>
                    <td class="align-top"><?php echo $row["name"];?></td>
                    <td class="align-top"><?php echo $row["address"];?></td>
                    <td class="align-top"><?php echo $row["area"];?></td>
                    <td class="align-top"><?php echo $row["price"];?></td>
                
                    
                    <td class="align-top"><?php echo htmlspecialchars($row["type"]);?></td>
                    <td class="align-top"><?php echo htmlspecialchars($row["facilities"]);?></td>
                    <td class="align-top text-center">
                        <a href="<?php echo $row["image"];?>" target="_blank">
                                <img src="<?php echo $row["image"]; ?>"
                                class="img-thumbnail" width="100"></a>

                    </td>
                    <td class="align-top"><?php echo $row["latitude"];?></td>
                    <td class="align-top"><?php echo $row["longitude"];?></td>
                <td>
                <?php 
                    if($row["status"] == "รอการอนุมัติ"){
                        echo "<span class='badge bg-warning'>รอการอนุมัติ</span>";
                    }elseif($row["status"] == "อนุมัติแล้ว"){
                        echo "<span class='badge bg-success'>อนุมัติแล้ว</span>";
                    }elseif($row["status"] == "ถูกปฏิเสธ"){
                        echo "<span class='badge bg-danger'>ถูกปฏิเสธ</span>";
                        if (!empty($row["reject_reason"])) {
                            echo "<div class='text-danger small mt-1'>เหตุผล: " . htmlspecialchars($row["reject_reason"]) . "</div>";
                        }
                    }
                ?>
                </td>


    
                  
                  


                </tr>
            <?php
                }
            ?>

        </table>
        </div>
        <a href="Place-Me.php">
            <button type="button" class="btn btn-outline-info">กลับหน้าหอพัก</button> 
        </a>
       
       
     
    </div>

    <div class="center">
        <p class="text-muted">หอพักที่คุณเพิ่มจะถูกเก็บไว้ในสถานะ "รอการอนุมัติ" จนกว่าแอดมินจะตรวจสอบและอนุมัติ ซึ่งอาจใช้เวลาถึง 24–72 ชั่วโมง โปรดแนบข้อมูลและรูปภาพที่ถูกต้องและไม่ละเมิดลิขสิทธิ์หรือกฎหมายใด ๆ หากรายการไม่ครบถ้วนหรือไม่เหมาะสม แอดมินอาจปฏิเสธหรือลบรายการได้โดยไม่แจ้งล่วงหน้า ระหว่างรอการอนุมัติ คุณสามารถแก้ไขข้อมูลได้ แต่การเปลี่ยนแปลงอาจต้องผ่านการตรวจสอบซ้ำ</p>
        <p class="text-muted">หากคุณมีคำถามหรือปัญหาเกี่ยวกับการเพิ่มหอพัก โปรดติดต่อฝ่ายสนับสนุนของเราที่
    </div>

    <?php
        
       
        include "footer.php";
        include "stipulation.php";
        
    ?>
   
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
</body>
</html>