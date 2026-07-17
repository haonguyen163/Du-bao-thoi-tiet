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
        
        <!-- 2. Menu Bản đồ (ĐÃ SỬA: Trỏ về Route ảo chuẩn MVC thay vì file .php vật lý) -->
        <li class="menu-item <?= (strpos($current_url, 'map') === 0) ? 'active' : ''; ?>" 
            onclick="window.location.href='/Weather_WebApp/map/index'">
            <i class="fa-solid fa-location-dot"></i> Bản đồ
        </li>
        
        <!-- 3. Menu Lịch sử -->
        <li class="menu-item <?= ($current_url == 'dashboard/history') ? 'active' : ''; ?>" 
            onclick="window.location.href='/Weather_WebApp/dashboard/history'">
            <i class="fa-solid fa-calendar-days"></i> Lịch sử
        </li>
        
        <!-- 4. Menu Cài đặt -->
        <li class="menu-item <?= ($current_url == 'dashboard/setting') ? 'active' : ''; ?>" 
            onclick="window.location.href='/Weather_WebApp/dashboard/setting'">
            <i class="fa-solid fa-gear"></i> Cài đặt
        </li>
        
        <!-- 5. Nút Đăng xuất -->
        <li class="menu-item" onclick="confirmLogout()">
            <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
        </li>
    </ul>
</nav>

<!-- JavaScript phục vụ việc đăng xuất dùng chung cho các trang sử dụng Sidebar -->
<script>
if (typeof confirmLogout !== 'function') {
    function confirmLogout() {
        if (confirm("Bạn có chắc chắn muốn đăng xuất khỏi hệ thống?")) {
            window.location.href = "/Weather_WebApp/auth/login";
        }
    }
}
</script>