<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bản đồ thời tiết 63 Tỉnh Thành - SkyCast Pro</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="/Weather_WebApp/public/css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        #weather-map {
            height: 85vh;
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            z-index: 1;
        }
        .bento-grid { display: block !important; }
        
        .leaflet-control-layers {
            font-family: 'Poppins', sans-serif;
            border-radius: 10px;
            padding: 10px;
        }

        /* STYLE ICON PHÁT SÁNG THEO TRẠNG THÁI THỜI TIẾT ĐỘNG */
        .weather-marker-icon {
            background: none;
            border: none;
        }
        .marker-bubble {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(4px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border: 2px solid #fff;
            transition: all 0.2s ease;
        }
        .marker-bubble i {
            font-size: 14px;
            margin-bottom: 1px;
        }
        .marker-bubble .marker-temp {
            font-size: 9px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1;
        }
        .marker-bubble:hover {
            transform: scale(1.2);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.25);
            z-index: 9999 !important;
        }
        
        /* Đồng bộ Class màu sắc từ API */
        .w-clear { color: #f59e0b; border-color: #f59e0b; }
        .w-clouds { color: #64748b; border-color: #94a3b8; }
        .w-rain { color: #3b82f6; border-color: #3b82f6; }
        .w-thunder { color: #7c3aed; border-color: #7c3aed; }
        .w-drizzle { color: #06b6d4; border-color: #06b6d4; }
        .w-mist { color: #0d9488; border-color: #0d9488; }

        .leaflet-popup-content-wrapper {
            font-family: 'Poppins', sans-serif;
            border-radius: 16px;
            padding: 6px;
        }
        .popup-info h4 { margin: 0 0 6px 0; color: #0f172a; font-size: 14px; }
        .popup-info p { margin: 4px 0; font-size: 12px; color: #475569; display: flex; align-items: center; gap: 6px; }
        .popup-info p i { width: 14px; color: #64748b; }
    </style>
</head>

<body>

    <div class="dashboard-container">
        <?php if (file_exists("../app/views/layouts/sidebar.php")) { require_once "../app/views/layouts/sidebar.php"; } ?>

        <main class="main-content">
            <header class="header">
                <div>
                    <h1>Bản đồ Khí tượng Toàn quốc (63 Tỉnh Thành)</h1>
                    <p>Dữ liệu quét trực tiếp từ OpenWeatherMap API vệ tinh toàn cầu.</p>
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
        const API_KEY = "e6dacaf9029357e7e8fc942a5b864ad5"; 

        // Đặt góc nhìn bao quát bản đồ Việt Nam
        var map = L.map('weather-map').setView([16.0000, 106.5000], 6); 

        var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        var cloudsLayer = L.tileLayer(`https://tile.openweathermap.org/map/clouds_new/{z}/{x}/{y}.png?appid=${API_KEY}`, { opacity: 0.4 });
        var rainLayer = L.tileLayer(`https://tile.openweathermap.org/map/precipitation_new/{z}/{x}/{y}.png?appid=${API_KEY}`, { opacity: 0.5 });
        var tempLayer = L.tileLayer(`https://tile.openweathermap.org/map/temp_new/{z}/{x}/{y}.png?appid=${API_KEY}`, { opacity: 0.3 });

        // 🛠️ TOÀN BỘ 63 TỈNH THÀNH VIỆT NAM KHÔNG THIẾU MỘT TỈNH NÀO
        const vietNamProvinces = [
            { name: "An Giang", lat: 10.5366, lon: 105.2155 },
            { name: "Bà Rịa - Vũng Tàu", lat: 10.4965, lon: 107.1691 },
            { name: "Bạc Liêu", lat: 9.2942, lon: 105.7274 },
            { name: "Bắc Giang", lat: 21.2731, lon: 106.1946 },
            { name: "Bắc Kạn", lat: 22.1471, lon: 105.8348 },
            { name: "Bắc Ninh", lat: 21.1861, lon: 106.0763 },
            { name: "Bến Tre", lat: 10.2435, lon: 106.3752 },
            { name: "Bình Dương", lat: 11.1627, lon: 106.6622 },
            { name: "Bình Định", lat: 13.9749, lon: 108.9734 },
            { name: "Bình Phước", lat: 11.7511, lon: 106.7562 },
            { name: "Bình Thuận", lat: 11.0916, lon: 108.0645 },
            { name: "Cà Mau", lat: 9.1769, lon: 105.1500 },
            { name: "Cao Bằng", lat: 22.6687, lon: 106.2577 },
            { name: "Cần Thơ", lat: 10.0452, lon: 105.7469 },
            { name: "Đà Nẵng", lat: 16.0544, lon: 108.2022 },
            { name: "Đắk Lắk", lat: 12.7100, lon: 108.2378 },
            { name: "Đắk Nông", lat: 12.1528, lon: 107.6767 },
            { name: "Điện Biên", lat: 21.6888, lon: 103.2059 },
            { name: "Đồng Nai", lat: 10.9574, lon: 106.9412 },
            { name: "Đồng Tháp", lat: 10.5186, lon: 105.6792 },
            { name: "Gia Lai", lat: 13.9845, lon: 108.1565 },
            { name: "Hà Giang", lat: 22.7483, lon: 104.9754 },
            { name: "Hà Nam", lat: 20.5456, lon: 105.9234 },
            { name: "Hà Nội", lat: 21.0285, lon: 105.8542 },
            { name: "Hà Tĩnh", lat: 18.2356, lon: 105.8078 },
            { name: "Hải Dương", lat: 20.9389, lon: 106.3142 },
            { name: "Hải Phòng", lat: 20.8449, lon: 106.6881 },
            { name: "Hậu Giang", lat: 9.7735, lon: 105.6321 },
            { name: "Hòa Bình", lat: 20.6845, lon: 105.4124 },
            { name: "TP. Hồ Chí Minh", lat: 10.7769, lon: 106.7009 },
            { name: "Hưng Yên", lat: 20.6463, lon: 106.0511 },
            { name: "Khánh Hòa", lat: 12.2388, lon: 109.1967 },
            { name: "Kiên Giang", lat: 9.9840, lon: 105.1259 },
            { name: "Kon Tum", lat: 14.3497, lon: 108.0039 },
            { name: "Lai Châu", lat: 22.3888, lon: 102.7845 },
            { name: "Lạng Sơn", lat: 21.8543, lon: 106.7612 },
            { name: "Lào Cai", lat: 22.4856, lon: 103.9706 },
            { name: "Lâm Đồng", lat: 11.9404, lon: 108.4583 },
            { name: "Long An", lat: 10.5936, lon: 106.3126 },
            { name: "Nam Định", lat: 20.4194, lon: 106.1683 },
            { name: "Nghệ An", lat: 19.2345, lon: 104.9734 },
            { name: "Ninh Bình", lat: 20.2506, lon: 105.9744 },
            { name: "Ninh Thuận", lat: 11.6378, lon: 108.8912 },
            { name: "Phú Thọ", lat: 21.3235, lon: 105.1963 },
            { name: "Phú Yên", lat: 13.0882, lon: 109.0911 },
            { name: "Quảng Bình", lat: 17.4834, lon: 106.5967 },
            { name: "Quảng Nam", lat: 15.5894, lon: 108.1094 },
            { name: "Quảng Ngãi", lat: 15.1206, lon: 108.7922 },
            { name: "Quảng Ninh", lat: 21.2356, lon: 107.2845 },
            { name: "Quảng Trị", lat: 16.7534, lon: 107.1056 },
            { name: "Sóc Trăng", lat: 9.6019, lon: 105.9726 },
            { name: "Sơn La", lat: 21.1926, lon: 103.9091 },
            { name: "Tây Ninh", lat: 11.3662, lon: 106.1183 },
            { name: "Thái Bình", lat: 20.4464, lon: 106.3364 },
            { name: "Thái Nguyên", lat: 21.5936, lon: 105.8442 },
            { name: "Thanh Hóa", lat: 20.0034, lon: 105.2967 },
            { name: "Thừa Thiên Huế", lat: 16.4637, lon: 107.5908 },
            { name: "Tiền Giang", lat: 10.4284, lon: 106.3263 },
            { name: "Trà Vinh", lat: 9.9364, lon: 106.3352 },
            { name: "Tuyên Quang", lat: 22.0563, lon: 105.2163 },
            { name: "Vĩnh Long", lat: 10.2452, lon: 105.9642 },
            { name: "Vĩnh Phúc", lat: 21.3614, lon: 105.5422 },
            { name: "Yên Bái", lat: 21.7235, lon: 104.8963 }
        ];

        var markersGroup = L.layerGroup().addTo(map);

        function getWeatherStyle(mainState) {
            switch (mainState.toLowerCase()) {
                case 'clear': return { icon: 'fa-sun', class: 'w-clear' };
                case 'clouds': return { icon: 'fa-cloud', class: 'w-clouds' };
                case 'rain': return { icon: 'fa-cloud-showers-heavy', class: 'w-rain' };
                case 'thunderstorm': return { icon: 'fa-cloud-bolt', class: 'w-thunder' };
                case 'drizzle': return { icon: 'fa-cloud-rain', class: 'w-drizzle' };
                case 'mist': case 'smoke': case 'haze': return { icon: 'fa-smog', class: 'w-mist' };
                default: return { icon: 'fa-cloud-sun', class: 'w-clouds' };
            }
        }

        
        function loadProvincesData() {
            let index = 0;
            function next() {
                if (index >= vietNamProvinces.length) return;
                const city = vietNamProvinces[index];
                const url = `https://api.openweathermap.org/data/2.5/weather?lat=${city.lat}&lon=${city.lon}&appid=${API_KEY}&units=metric&lang=vi`;

                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        if (data.weather && data.weather.length > 0) {
                            const mainState = data.weather[0].main;
                            const description = data.weather[0].description;
                            const tempVal = Math.round(data.main.temp);
                            const humidity = data.main.humidity;
                            const windSpeed = data.wind.speed;

                            const style = getWeatherStyle(mainState);

                            var customIcon = L.divIcon({
                                html: `<div class="marker-bubble ${style.class}">
                                        <i class="fa-solid ${style.icon}"></i>
                                        <span class="marker-temp">${tempVal}°</span>
                                       </div>`,
                                className: 'weather-marker-icon',
                                iconSize: [38, 38],
                                iconAnchor: [19, 19]
                            });

                            var popupContent = `
                                <div class="popup-info">
                                    <h4>${city.name}</h4>
                                    <p><i class="fa-solid fa-smog"></i> <b>Trạng thái:</b> ${description.charAt(0).toUpperCase() + description.slice(1)}</p>
                                    <p><i class="fa-solid fa-temperature-half"></i> <b>Nhiệt độ:</b> ${tempVal}°C</p>
                                    <p><i class="fa-solid fa-droplet"></i> <b>Độ ẩm:</b> ${humidity}%</p>
                                    <p><i class="fa-solid fa-wind"></i> <b>Tốc độ gió:</b> ${windSpeed} m/s</p>
                                </div>
                            `;

                            L.marker([city.lat, city.lon], { icon: customIcon })
                                .bindPopup(popupContent)
                                .addTo(markersGroup);
                        }
                        index++;
                        setTimeout(next, 40); 
                    })
                    .catch(() => {
                        index++;
                        next();
                    });
            }
            next();
        }

        
        loadProvincesData();

        var baseMaps = { "Bản đồ nền": osm };
        var overlayMaps = {
            "📍 Hệ thống 63 tỉnh thành": markersGroup,
            "☁️ Mây bao phủ": cloudsLayer,
            "🌧️ Lượng mưa": rainLayer,
            "🌡️ Lớp nhiệt độ": tempLayer
        };

        L.control.layers(baseMaps, overlayMaps).addTo(map);
        rainLayer.addTo(map); // Mặc định bật radar lượng mưa

        function confirmLogout() {
            if (confirm("Bạn có chắc chắn muốn đăng xuất khỏi hệ thống?")) {
                window.location.href = "/Weather_WebApp/auth/logout";
            }
        }
    </script>
</body>
</html>