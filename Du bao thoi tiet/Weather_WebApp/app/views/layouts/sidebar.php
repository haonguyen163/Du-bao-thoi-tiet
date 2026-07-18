<?php
// Lấy đoạn URL hiện tại để kiểm tra active menu động
$current_url = $_GET['url'] ?? '';
$current_url = strtolower(trim($current_url, '/'));
?>
<nav class="sidebar">
    <div class="logo">
       <a href="/Weather_WebApp/dashboard/index"><i class="fa-solid fa-cloud-bolt"></i> SkyCast</a> 
    </div>
    <ul class="menu-list">
        <!-- 1. Menu Tổng quan -->
        <li class="menu-item <?= ($current_url == 'dashboard/index' || $current_url == 'dashboard' || empty($current_url)) ? 'active' : ''; ?>" 
            onclick="window.location.href='/Weather_WebApp/dashboard/index'">
            <i class="fa-solid fa-chart-pie"></i> Tổng quan
        </li>
        
        <!-- 2. Menu Bản đồ  -->
        <li class="menu-item <?= (strpos($current_url, 'map') === 0) ? 'active' : ''; ?>" 
            onclick="window.location.href='/Weather_WebApp/map/index'">
            <i class="fa-solid fa-location-dot"></i> Bản đồ
        </li>
        
        <!-- 3. Menu Cài đặt -->
        <!-- <li class="menu-item <?= ($current_url == 'dashboard/setting') ? 'active' : ''; ?>" 
            onclick="window.location.href='/Weather_WebApp/dashboard/setting'">
            <i class="fa-solid fa-gear"></i> Cài đặt
        </li> -->
        
        <!-- 4. Nút Đăng xuất Custom Đẹp Mắt -->
        <li class="menu-item" onclick="openLogoutModal()">
            <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
        </li>
    </ul>
</nav>

<!-- ================= KHUNG ĐĂNG XUẤT CUSTOM BO GÓC BÓNG BẨY ================= -->
<div id="logoutModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(4px); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: #ffffff; padding: 30px; border-radius: 24px; width: 360px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); border: 1px solid rgba(226, 232, 240, 0.8); text-align: center; animation: popUp 0.25s ease;">
        <div style="width: 60px; height: 60px; background: #ffebee; color: #c62828; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 20px auto; font-size: 24px;">
            <i class="fa-solid fa-right-from-bracket"></i>
        </div>
        <h3 style="font-size: 18px; font-weight: 600; color: #1e293b; margin-bottom: 10px; font-family: 'Poppins', sans-serif;">Đăng xuất?</h3>
        <p style="font-size: 14px; color: #64748b; margin-bottom: 25px; line-height: 1.5; font-family: 'Poppins', sans-serif;">Bạn có chắc chắn muốn đăng xuất khỏi hệ thống SkyCast?</p>
        
        <div style="display: flex; gap: 12px; justify-content: center;">
            <button onclick="closeLogoutModal()" style="flex: 1; padding: 12px; border-radius: 14px; border: 1px solid #e2e8f0; background: #f8fafc; color: #64748b; font-weight: 500; cursor: pointer; font-size: 14px; font-family: 'Poppins', sans-serif;">Hủy</button>
            <a href="/Weather_WebApp/auth/logout" style="flex: 1; padding: 12px; border-radius: 14px; border: none; background: linear-gradient(135deg, #ef4444, #dc2626); color: white; font-weight: 500; text-decoration: none; display: inline-block; font-size: 14px; line-height: 1.5; font-family: 'Poppins', sans-serif;">Đăng xuất</a>
        </div>
    </div>
</div>

<style>
@keyframes popUp { 
    from { transform: scale(0.92); opacity: 0; } 
    to { transform: scale(1); opacity: 1; } 
}
</style>

<script>
function openLogoutModal() {
    document.getElementById('logoutModal').style.display = 'flex';
}

function closeLogoutModal() {
    document.getElementById('logoutModal').style.display = 'none';
}

// Đóng modal nếu click trượt ra vùng nền mờ phía ngoài
window.addEventListener('click', function(event) {
    const modal = document.getElementById('logoutModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});
</script>