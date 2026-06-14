<?php
session_start();
// Nếu chưa có session user_id (chưa đăng nhập) thì đá về trang login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); // Đường dẫn quay ra thư mục cha để vào login
    exit();
}
// Nếu đã đăng nhập, code trang home ở dưới đây...
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkyCast Pro Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../public/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<style>
        /* Đảm bảo bản đồ full màn hình phần content */
        #weather-map {
            height: 800px; /* Chiều cao bản đồ */
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
<body>

    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="logo">
                <a href="home.php"><i class="fa-solid fa-cloud-bolt"></i> SkyCast</a>
            </div>
            <ul class="menu-list">
                <li class="menu-item" onclick="window.location.href='home.php'"><i class="fa-solid fa-chart-pie"></i> Tổng quan</li>
                <li class="menu-item active "><i class="fa-solid fa-location-dot"></i> Bản đồ</li>
                <li class="menu-item" onclick="window.location.href='history.php'"><i class="fa-solid fa-calendar-days"></i> Lịch sử</li>
                <li class="menu-item" onclick="window.location.href='setting.php'"><i class="fa-solid fa-gear"></i> Cài đặt</li>
                <li class="menu-item" onclick="confirmLogout()">
                    <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                </li>
        </nav>

        <main class="main-content">
            <header class="header">
                <div>
                    <h1>Xin chào, <?= $_SESSION['username'] ?>!</h1>
                    <p>Chào mừng bạn quay trở lại.</p>
                </div>
                <div class="user-profile">
                    <div class="search-bar">
                        <i class="fa-solid fa-magnifying-glass" style="color: #ccc;"></i>
                        <input type="text" placeholder="Tìm thành phố...">
                    </div>
                    <div class="avatar">
                        <img src="https://ui-avatars.com/api/?name=User&background=0D8ABC&color=fff" alt="User">
                    </div>
                </div>
            </header>

            <div class="bento-grid">

                <div class="card main-weather-card">
                    <div id="weather-map"></div>
                </div>
            </div>
        </main>
        
    </div>
    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Đăng xuất?',
                text: "Bạn có chắc chắn muốn thoát phiên làm việc?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Đăng xuất ngay',
                cancelButtonText: 'Ở lại'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../logout.php';
                }
            })
        }
    </script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // 1. CẤU HÌNH API KEY (Thay key của bạn vào đây)
        const API_KEY = "e6dacaf9029357e7e8fc942a5b864ad5";

        // 2. KHỞI TẠO BẢN ĐỒ (Tọa độ mặc định: TP.HCM)
        // setView([Vĩ độ, Kinh độ], Độ zoom)
        var map = L.map('weather-map').setView([10.7769, 106.7009], 6);

        // 3. THÊM LỚP NỀN (BASE MAP) - Dùng OpenStreetMap (Miễn phí)
        var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // 4. THÊM CÁC LỚP THỜI TIẾT TỪ OWM

        // Lớp Mây (Clouds)
        var clouds = L.tileLayer(`https://tile.openweathermap.org/map/clouds_new/{z}/{x}/{y}.png?appid=${API_KEY}`, {
            opacity: 0.8
        });

        // Lớp Mưa (Precipitation)
        var rain = L.tileLayer(`https://tile.openweathermap.org/map/precipitation_new/{z}/{x}/{y}.png?appid=${API_KEY}`, {
            opacity: 0.7
        });

        // Lớp Nhiệt độ (Temperature)
        var temp = L.tileLayer(`https://tile.openweathermap.org/map/temp_new/{z}/{x}/{y}.png?appid=${API_KEY}`, {
            opacity: 0.6
        });

        // Lớp Tốc độ gió (Wind Speed)
        var wind = L.tileLayer(`https://tile.openweathermap.org/map/wind_new/{z}/{x}/{y}.png?appid=${API_KEY}`, {
            opacity: 0.6
        });

        // Lớp Áp suất (Pressure)
        var pressure = L.tileLayer(`https://tile.openweathermap.org/map/pressure_new/{z}/{x}/{y}.png?appid=${API_KEY}`, {
            opacity: 0.6
        });

        // 5. TẠO BỘ ĐIỀU KHIỂN ĐỂ BẬT/TẮT CÁC LỚP
        var baseMaps = {
            "Bản đồ thường": osm
        };

        var overlayMaps = {
            "☁️ Mây bao phủ": clouds,
            "🌧️ Lượng mưa": rain,
            "🌡️ Nhiệt độ": temp,
            "🍃 Tốc độ gió": wind,
            "⏲️ Áp suất": pressure
        };

        // Thêm widget chọn layer vào góc trên bên phải
        L.control.layers(baseMaps, overlayMaps).addTo(map);

        // Mặc định bật lớp Mưa và Nhiệt độ lên cho sinh động
        rain.addTo(map);
        temp.addTo(map);
    </script>
</body>

</html>