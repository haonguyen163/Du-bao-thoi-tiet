<?php
// --- BỘ SÀNG LỌC VÀ CHUẨN HÓA DỮ LIỆU ĐẦU VÀO (TÁCH BIỆT KHỎI HTML) ---
$username = htmlspecialchars($_SESSION['username'] ?? 'User');
$city = htmlspecialchars($data['weather']['city'] ?? '1');
$temperature = htmlspecialchars($data['weather']['temperature'] ?? '30');
$humidity = htmlspecialchars($data['weather']['humidity'] ?? '70');
$wind_speed = htmlspecialchars($data['weather']['wind_speed'] ?? '5');

// Tránh sử dụng toán tử điều kiện phức tạp trực tiếp bên trong attribute HTML
$advisor_code = $data['ai']['advisor_code'] ?? 0;
$bridge_desc = ($advisor_code == 1) ? 'Có mưa rải rác' : 'Nắng đẹp thoáng đãng';
$ai_advice = htmlspecialchars($data['ai']['advice'] ?? 'Thời tiết ổn định 🌤️');

// Xử lý icon thời tiết động dạng chuỗi thuần túy trước khi render
$weather_icon_class = ($advisor_code == 1) ? 'fa-cloud-showers-heavy' : 'fa-sun';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkyCast Pro Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/style.css"> <!-- Kế thừa CSS cũ giữ nguyên[cite: 4] -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Cậu đè dòng này vào chỗ nhúng CSS cũ ở đầu file user.php nhé -->
<link rel="stylesheet" href="/Weather_WebApp/public/css/style.css">
</head>
<body>

    <!-- 🌟 THẺ CẦU NỐI DỮ LIỆU ĐỘC LẬP (ĐÃ LÀM SẠCH VẠCH ĐỎ) -->
    <div id="weather-data-bridge" 
         data-city="<?= $city ?>"
         data-temp="<?= $temperature ?>"
         data-hum="<?= $humidity ?>"
         data-wind="<?= $wind_speed ?>"
         data-desc="<?= $bridge_desc ?>">
    </div>

    <div class="dashboard-container">
        <!-- Nhúng Sidebar dùng chung -->
        <?php require_once "../app/views/layouts/sidebar.php"; ?>

        <main class="main-content">
            <header class="header">
                <div>
                    <h1>Xin chào, <?= $username ?>!</h1>
                    <p>Chào mừng bạn quay trở lại hệ thống SkyCast Pro.</p>
                </div>
                <div class="user-profile">
                    <div class="avatar">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username'] ?? 'User') ?>&background=0D8ABC&color=fff" alt="User">
                    </div>
                </div>
            </header>

            <!-- Khung báo lỗi Server Python (nếu có) -->
            <?php if (isset($data['ai']['success']) && !$data['ai']['success']): ?>
                <div style="color: red; padding: 15px; background: #fff0f0; border-radius: 15px; margin-bottom: 25px;">
                    <i class="fa-solid fa-triangle-exclamation"></i> <b>Lỗi kết nối AI:</b> <?= htmlspecialchars($data['ai']['error']) ?>
                </div>
            <?php endif; ?>

            <div class="bento-grid">
                <!-- Ô 1: Thời tiết hiện tại (Dữ liệu từ dòng cuối CSV) -->
                <div class="card main-weather-card">
                    <div class="card-header">
                        <span class="card-title"><i class="fa-solid fa-location-dot"></i> ID Thành phố: <?= $city ?></span>
                        <span class="view-all" style="background: #27ae60; color: white; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: bold;">
                            <?= $ai_advice ?>
                        </span>
                    </div>
                    <div class="temp-display">
                        <div class="big-temp"><?= $temperature ?>°C</div>
                        <!-- Sử dụng biến class đã qua xử lý lọc chuỗi thô để tránh lỗi quét attribute HTML -->
                        <i class="fa-solid <?= $weather_icon_class ?> big-icon"></i>
                    </div>
                    <div class="weather-meta">
                        <div class="meta-item"><i class="fa-solid fa-droplet"></i> Độ ẩm: <?= $humidity ?>%</div>
                        <div class="meta-item"><i class="fa-solid fa-wind"></i> Gió: <?= $wind_speed ?> m/s</div>
                    </div>
                </div>

                <!-- Ô 2: Biểu đồ nhiệt độ theo Giờ (AI Hourly Forecast) -->
                <div class="card chart-card">
                    <div class="card-header">
                        <span class="card-title">Dự báo chi tiết các giờ tới (AI)</span>
                    </div>
                    <div class="chart-bars">
                        <?php if (!empty($data['ai']['hourly_forecast'])): ?>
                            <?php foreach(array_slice($data['ai']['hourly_forecast'], 0, 6) as $h_cast): ?>
                                <?php 
                                    $h_temp = intval($h_cast['temp']);
                                    $bar_height = min(100, max(25, $h_temp * 2.5));
                                ?>
                                <div class="bar-col">
                                    <div class="bar active" style="height: <?= $bar_height ?>px;"></div>
                                    <span class="time-label"><?= htmlspecialchars($h_cast['time']) ?><br><b><?= $h_temp ?>°C</b></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color:#999; font-size: 13px; padding-top:20px;">Không có dữ liệu dự báo giờ.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Ô 3: Danh sách dự báo các ngày tới (AI Weekly) -->
                <div class="card forecast-list-card">
                    <div class="card-header">
                        <span class="card-title">Dự báo 7 ngày tới (AI)</span>
                    </div>
                    <?php if (!empty($data['ai']['weekly_forecast'])): ?>
                        <?php foreach($data['ai']['weekly_forecast'] as $w_cast): ?>
                            <?php 
                                $w_temp = floatval($w_cast['data']['temperature']);
                                $w_rain = floatval($w_cast['data']['precipitation']);
                                $w_icon = ($w_rain > 0.5) ? 'fa-cloud-rain' : 'fa-sun';
                                $w_color = ($w_temp > 30) ? 'orange' : '#0D8ABC';
                            ?>
                            <div class="forecast-row">
                                <span class="f-day">Ngày <?= htmlspecialchars($w_cast['date']) ?></span>
                                <i class="fa-solid <?= $w_icon ?> f-icon" style="color: <?= $w_color ?>;"></i>
                                <span class="f-temp"><?= $w_temp ?>°C</span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color:#999; font-size: 13px; text-align:center; padding: 20px;">Đang chờ dữ liệu tuần...</p>
                    <?php endif; ?>
                </div>

                <!-- Ô 4 & 5: Các ô Stats chỉ số phụ -->
                <div class="card stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-eye"></i></div>
                    <div class="stat-value">10km</div>
                    <div class="stat-label">Tầm nhìn trung bình</div>
                </div>
                <div class="card stat-card">
                    <div class="stat-icon" style="color: #e74c3c;"><i class="fa-solid fa-sun"></i></div>
                    <div class="stat-value">High</div>
                    <div class="stat-label">UV Index</div>
                </div>

                <!-- Ô 6: Tích Hợp Hộp Chat Tư Vấn Dữ Liệu Với Google Gemini AI -->
                <div class="card promo-card" style="grid-column: 2 / 4; background: white; color: #2c3e50; border: 1px solid rgba(0,0,0,0.08);">
                    <h3 style="color: #4facfe; font-size: 16px; margin-bottom: 8px;"><i class="fa-solid fa-brain"></i> Trợ lý ảo tư vấn SkyCast AI</h3>
                    <div id="chat-box-area" style="height: 140px; overflow-y: auto; padding: 12px; background: #f4f7f6; border-radius: 15px; margin-bottom: 10px; font-size: 13px; line-height: 1.5;">
                        <p style="color: #7f8c8d;"><i>Chào bạn! Hãy đặt câu hỏi để nhận lời khuyên trang phục hoặc lịch trình phù hợp với thời tiết hiện tại nhé 🌤️...</i></p>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="user-chat-input" placeholder="Hôm nay có nên đi đá bóng không?..." style="flex: 1; padding: 12px; border-radius: 12px; border: 1px solid #ddd; outline: none; font-size: 13px;">
                        <button onclick="askGeminiAI()" style="padding: 10px 20px; border-radius: 12px; border: none; background: var(--accent-gradient); color: white; cursor: pointer; font-weight: bold; font-size: 13px;">Hỏi AI</button>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Nhúng Footer chứa script logout -->
    <?php require_once "../app/views/layouts/footer.php"; ?>

    <!-- JAVASCRIPT THUẦN CHUẨN FRONTEND - KHÔNG CÒN LỖI PHÂN TÍCH CÚ PHÁP -->
    <script>
    async function askGeminiAI() {
        let inputField = document.getElementById('user-chat-input');
        let question = inputField.value.trim();
        if(!question) return;

        let box = document.getElementById('chat-box-area');
        box.innerHTML += `<p style="margin-bottom: 6px;"><b>Bạn:</b> ${question}</p>`;
        inputField.value = '';
        box.scrollTop = box.scrollHeight;

        // Đọc dữ liệu an toàn từ thẻ ẩn HTML Bridge ở phía trên
        let bridge = document.getElementById('weather-data-bridge');

        let weatherContext = {
            "name": "Trạm đo số " + bridge.dataset.city,
            "temp": bridge.dataset.temp,
            "hum": bridge.dataset.hum,
            "wind": bridge.dataset.wind,
            "desc": bridge.dataset.desc
        };

        try {
            let res = await fetch('http://127.0.0.1:5000/chat', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ message: question, context: weatherContext })
            });
            let replyData = await res.json();
            box.innerHTML += `<p style="color: #0d8abc; margin-bottom: 6px;"><b>SkyCast AI:</b> ${replyData.response}</p>`;
            box.scrollTop = box.scrollHeight;
        } catch(err) {
            box.innerHTML += `<p style="color: red; font-style: italic;">⚠️ Không kết nối được tới máy chủ AI Server!</p>`;
        }
    }
    </script>
</body>
</html>