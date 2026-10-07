<!-- Stipulation Modal -->
<div class="modal fade" id="stipulationModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">ข้อกำหนดในการเพิ่มหอพัก</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <ul>
          <li>หอพักต้องมีข้อมูลครบถ้วนและถูกต้อง</li>
          <li>รูปภาพต้องมีความชัดเจนและแสดงรายละเอียดของหอพัก</li>
          <li>หอพักต้องเป็นไปตามกฎหมายและไม่มีการละเมิดลิขสิทธิ์</li>
          <li>หอพักต้องได้รับการอนุมัติจากทีมงานก่อนที่จะปรากฏบนเว็บไซต์</li>
        </ul>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" id="acceptStipulation">ยอมรับ</button>
      </div>

    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const acceptBtn = document.getElementById('acceptStipulation');
    if (acceptBtn) {
      acceptBtn.addEventListener('click', function() {
        console.log('ผู้ใช้ยอมรับข้อกำหนด');
      });
    }
  });
</script>