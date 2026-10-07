<?php if (isset($_SESSION['user'])): ?>
<?php
include_once "connect.php";
$notifUserId = intval($_SESSION['user']['id']);
$unreadCount = 0;
$unreadRes = mysqli_query($con, "SELECT COUNT(*) c FROM notifications WHERE user_id={$notifUserId} AND is_read=0");
if ($unreadRes) { $unreadCount = (int) mysqli_fetch_assoc($unreadRes)['c']; }
?>
<div class="notif-bell-wrap">
    <span class="notif-bell" onclick="toggleNotifMenu()">
        🔔
        <?php if ($unreadCount > 0): ?>
            <span class="notif-badge"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span>
        <?php endif; ?>
    </span>
    <div class="notif-menu" id="notif-menu">
        <div class="notif-header">
            <span>การแจ้งเตือน</span>
            <a href="javascript:void(0)" onclick="markAllNotifRead()" class="notif-clear">อ่านทั้งหมด</a>
        </div>
        <div id="notif-list" class="notif-list">กำลังโหลด...</div>
    </div>
</div>

<style>
.notif-bell-wrap{position:relative;display:inline-block;margin-right:12px;}
.notif-bell{font-size:20px;cursor:pointer;position:relative;}
.notif-badge{position:absolute;top:-6px;right:-8px;background:#e63946;color:#fff;border-radius:50%;
    font-size:10px;padding:1px 5px;font-weight:700;}
.notif-menu{display:none;position:absolute;right:0;top:32px;width:300px;max-height:360px;overflow-y:auto;
    background:#fff;border-radius:10px;box-shadow:0 6px 24px rgba(0,0,0,.15);z-index:999;}
.notif-menu.show{display:block;}
.notif-header{display:flex;justify-content:space-between;align-items:center;padding:10px 14px;
    border-bottom:1px solid #eee;font-weight:600;font-size:14px;}
.notif-clear{font-size:12px;font-weight:400;color:#4FB8C4;text-decoration:none;}
.notif-list{padding:6px 0;}
.notif-item{padding:10px 14px;font-size:13px;border-bottom:1px solid #f4f4f4;color:#333;}
.notif-item.unread{background:#eef8fa;}
.notif-time{font-size:11px;color:#999;margin-top:2px;}
.notif-empty{padding:20px;text-align:center;color:#999;font-size:13px;}
</style>

<script>
function toggleNotifMenu() {
    const menu = document.getElementById('notif-menu');
    menu.classList.toggle('show');
    if (menu.classList.contains('show')) loadNotifications();
}
document.addEventListener('click', function(e){
    const wrap = document.querySelector('.notif-bell-wrap');
    const menu = document.getElementById('notif-menu');
    if (wrap && menu && !wrap.contains(e.target)) menu.classList.remove('show');
});

function loadNotifications() {
    fetch('connect.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=getNotifications'
    })
    .then(r => r.json())
    .then(data => {
        const list = document.getElementById('notif-list');
        if (!data.success || data.notifications.length === 0) {
            list.innerHTML = '<div class="notif-empty">ไม่มีการแจ้งเตือน</div>';
            return;
        }
        list.innerHTML = data.notifications.map(n => `
            <div class="notif-item ${n.is_read == 0 ? 'unread' : ''}">
                ${n.message}
                <div class="notif-time">${n.created_at}</div>
            </div>
        `).join('');
    });
}

function markAllNotifRead() {
    fetch('connect.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=markNotificationsRead'
    })
    .then(r => r.json())
    .then(() => location.reload());
}
</script>
<?php endif; ?>