<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bản đồ thời tiết - SkyCast Pro</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- 🛠️ ĐÃ SỬA: Link CSS tuyệt đối để tránh vỡ giao diện trên Router ảo -->
    <link rel="stylesheet" href="/Weather_WebApp/public/css/style.css">
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        /* Đảm bảo bản đồ full màn hình phần content */
        #weather-map {
            height: 85vh; /* Chiều cao bản đồ */
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            z-index: 1;
        }
        .bento-grid { display: block !important; } /* Ghi đè grid để hiển thị map to */
        
        /* Chỉnh lại cái control chọn lớp bản đồ cho đẹp */
        .leaflet-control-layers {
            font-family: 'Poppins', sans-serif;
            border-radius: 10px;
            padding: 10px;
        }
    </style>
</head>

<body>

    <div class="dashboard-container">
        <!-- 🛠️ ĐÃ SỬA: Đồng bộ Menu Sidebar theo các route ảo MVC -->
        <nav class="sidebar">
            <div class="logo">
                <i class="fa-solid fa-cloud-bolt"></i> SkyCast
            </div>
            <ul class="menu-list">
                <li class="menu-item" onclick="window.location.href='/Weather_WebApp/dashboard/index'"><i class="fa-solid fa-chart-pie"></i> Tổng quan</li>
                <li class="menu-item active"><i class="fa-solid fa-location-dot"></i> Bản đồ</li>
                <li class="menu-item" onclick="window.location.href='/Weather_WebApp/dashboard/history'"><i class="fa-solid fa-calendar-days"></i> Lịch sử</li>
                <li class="menu-item" onclick="window.location.href='/Weather_WebApp/dashboard/setting'"><i class="fa-solid fa-gear"></i> Cài đặt</li>
                <li class="menu-item" onclick="confirmLogout()">
                    <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                </li>
            </ul>
        </nav>

        <main class="main-content">
            <header class="header">
                <div>
                    <h1>Bản đồ Khí tượng</h1>
                    <p>Theo dõi mây, mưa, nhiệt độ toàn cầu.</p>
                </div>
                <div class="user-profile">
                    <div class="avatar">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username'] ?? 'User') ?>&background=0D8ABC&color=fff" alt="User">
                    </div>
                </div>
            </header>

            <div id="weather-map"></div>

        </main>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // 1. CẤU HÌNH API KEY
        const API_KEY = "e6dacaf9029357e7e8fc942a5b864ad5"; 

        // 2. KHỞI TẠO BẢN ĐỒ (Tọa độ mặc định: TP.HCM)
        var map = L.map('weather-map').setView([10.7769, 106.7009], 6);

        // 3. THÊM LỚP NỀN (BASE MAP) - Dùng OpenStreetMap
        var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // 4. THÊM CÁC LỚP THỜI TIẾT TỪ OWM
        var clouds = L.tileLayer(`https://tile.openweathermap.org/map/clouds_new/{z}/{x}/{y}.png?appid=${API_KEY}`, { opacity: 0.8 });
        var rain = L.tileLayer(`https://tile.openweathermap.org/map/precipitation_new/{z}/{x}/{y}.png?appid=${API_KEY}`, { opacity: 0.7 });
        var temp = L.tileLayer(`https://tile.openweathermap.org/map/temp_new/{z}/{x}/{y}.png?appid=${API_KEY}`, { opacity: 0.6 });
        var wind = L.tileLayer(`https://tile.openweathermap.org/map/wind_new/{z}/{x}/{y}.png?appid=${API_KEY}`, { opacity: 0.6 });
        var pressure = L.tileLayer(`https://tile.openweathermap.org/map/pressure_new/{z}/{x}/{y}.png?appid=${API_KEY}`, { opacity: 0.6 });

        // 5. TẠO BỘ ĐIỀU KHIỂN ĐỂ BẬT/TẮT CÁC LỚP
        var baseMaps = { "Bản đồ thường": osm };
        var overlayMaps = {
            "☁️ Mây bao phủ": clouds,
            "🌧️ Lượng mưa": rain,
            "🌡️ Nhiệt độ": temp,
            "🍃 Tốc độ gió": wind,
            "⏲️ Áp suất": pressure
        };

        L.control.layers(baseMaps, overlayMaps).addTo(map);

        // Mặc định bật lớp Mưa và Nhiệt độ lên cho sinh động
        rain.addTo(map);
        temp.addTo(map);

        // 🛠️ HÀM ĐĂNG XUẤT AN TOÀN ĐỒNG BỘ
        function confirmLogout() {
            if (confirm("Bạn có chắc chắn muốn đăng xuất khỏi hệ thống?")) {
                window.location.href = "/Weather_WebApp/auth/logout";
            }
        }
    </script>
</body>
</html>