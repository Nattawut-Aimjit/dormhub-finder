<?php
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: viewhomepage.php");
    exit();
    }
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin-Status</title>
    <!--ติดตั้ง js css ในเครื่อง--->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 15px 10px;
                overflow-x: auto;
            }

            .container p {
                font-size: 24px;
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
        }

        @media (max-width: 480px) {
            body {
                margin: 0;
                padding: 0;
            }

            .container {
                padding: 10px 8px;
                max-width: 100%;
            }

            .container p {
                font-size: 18px;
                margin: 10px 0;
            }

            .table {
                font-size: 10px;
                table-layout: auto;
            }

            .table th {
                font-size: 9px;
                padding: 6px 2px;
            }

            .table td {
                padding: 6px 2px;
                word-break: break-word;
            }

            .btn {
                padding: 4px 8px;
                font-size: 11px;
            }
        }
    </style>
</head>


    <div class="container">
        <p>Admin</p>
        <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead>
                <tr class="table-primary">
                    <td class="text-center">ชื่อที่พัก</td>
                    <td class="text-center">ที่อยู่</td>
                    <td class="text-center">พื้นที่ใกล้เคียง</td>
                    <td class="text-center">ราคา</td>
                    <td class="text-center">ประเภท</td>
                    <td class="text-center">สิ่งอำนวยความสะดวก</td>
                    <td class="text-center">รูปภาพ</td>
                    <td class="text-center">ละติจูด</td>
                    <td class="text-center">ลองจิจูด</td>
                    <td class="text-center">ผู้ใช้</td>
                    <td class="text-center">สถานะ</td>
                    <td class="text-center">ลบ</td>

                    
                  
                </tr>
            </thead>
            <?php
           
            include "connect.php";
            $sql="SELECT d.id, d.name, d.price, d.water_rate, d.electricity_rate, d.contact_link, d.status, d.user_id, 
		    l.address, l.area, l.latitude, l.longitude, 
		    t.type_name AS type, 
		    (SELECT image_path FROM dorm_images WHERE dorm_id = d.id AND is_main = 1 LIMIT 1) AS image, 
		    (SELECT GROUP_CONCAT(facility_name SEPARATOR ',') FROM dorm_facilities WHERE dorm_id = d.id) AS facilities 
		    FROM dormitories d 
		    LEFT JOIN dorm_locations l ON d.id = l.dorm_id 
		    LEFT JOIN dorm_types t ON d.type_id = t.id 
		    ORDER BY d.id ASC";
            $result=mysqli_query($con,$sql) or die (mysqli_error($con));
            while($row=mysqli_fetch_array($result)) //ดึงข้อมูลออกจาก array $result ทีละ record
                {
                    if(empty($row["name"])) continue;
            ?>
    <tr>
                    <td class="align-top"><?php echo $row["name"];?></td>
                    <td class="align-top"><?php echo $row["address"];?></td>
                    <td class="align-top"><?php echo $row["area"];?></td>
                    <td class="align-top"><?php echo $row["price"];?></td>
                
                    
                    <td class="align-top"><?php if ($row["type"]==1) {echo "หอพัก";} else if ($row["type"]==2) {echo "อพาร์ทเม้นท์";} else if ($row["type"]==3) {echo "คอนโด";} else if ($row["type"]==4) {echo "บ้านพัก";}?></td>
                    <td class="align-top"><?php echo $row["facilities"];?></td>
                    <td class="align-top text-center">
                        <a href="<?php echo $row["image"];?>" target="_blank">
                                <img src="<?php echo $row["image"]; ?>"
                                class="img-thumbnail" width="100"></a>

                    </td>
                    <td class="align-top"><?php echo $row["latitude"];?></td>
                    <td class="align-top"><?php echo $row["longitude"];?></td>
    
                   
                    <td class="align-top"><?php echo $row["user_id"];?></td>
    <td class="align-top text-center">

        <?php if ($row["status"] == "รอการอนุมัติ") { ?>

        <a href="admin-update.php?id=<?php echo $row["id"]; ?>&status=approve"
            class="btn btn-success btn-sm"
            onclick="return confirm('ยืนยันการอนุมัติ?');">
            อนุมัติ
        </a>

        <a href="javascript:void(0)"
            class="btn btn-danger btn-sm"
            onclick="rejectDorm(<?php echo $row["id"]; ?>)">
            ปฏิเสธ
        </a>

        <?php } else { ?>

        <?php echo $row["status"]; ?>

        <?php } ?>
    </td>
        <td class="align-top text-center">
            <a href="admin-delete.php?id=<?php echo $row["id"]; ?>"
   onclick="return confirm('คุณต้องการลบหรือไม่?');"
   class="btn btn-outline-danger">
   🗑️
</a>


        </td>
    </tr>
            <?php
                }
            ?>

        </table>
        </div>
        <a href="admin-dashboard.php">
            <button type="button" class="btn btn-outline-primary">แดชบอร์ด</button> 
        </a>
        <a href="viewhomepage.php">
            <button type="button" class="btn btn-outline-info">ออกจากระบบ</button> 
        </a>
       
    </div>

   
<script>
function rejectDorm(id) {
    const reason = prompt("กรุณาระบุเหตุผลที่ปฏิเสธที่พักนี้:");
    if (reason === null) return;
    window.location = "admin-update.php?id=" + id + "&status=reject&reason=" + encodeURIComponent(reason);
}
</script>

</body>
</html>