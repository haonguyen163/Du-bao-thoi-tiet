<?php
// --- BỘ SÀNG LỌC VÀ CHUẨN HÓA DỮ LIỆU ĐẦU VÀO ---
$username = htmlspecialchars($_SESSION['username'] ?? 'User');
$city = htmlspecialchars($data['weather']['city'] ?? '1');
$temperature = htmlspecialchars($data['weather']['temperature'] ?? '30');
$humidity = htmlspecialchars($data['weather']['humidity'] ?? '70');
$wind_speed = htmlspecialchars($data['weather']['wind_speed'] ?? '5');
$precipitation = htmlspecialchars($data['weather']['precipitation'] ?? '0');

// Phân loại logic AI cổ điển nhận từ Flask Server
$advisor_code = $data['ai']['advisor_code'] ?? 0;
$bridge_desc = ($advisor_code == 1) ? 'Mây rải rác / Có mưa' : 'Nắng đẹp thoáng đãng';
$ai_badge_text = ($advisor_code == 1) ? 'AI: Thời tiết xấu, nên ở nhà!' : 'AI: Trời đẹp, thích hợp đi chơi! ☀️';
$ai_badge_class = ($advisor_code == 1) ? 'badge-danger' : 'badge-success';

$ai_advice = htmlspecialchars($data['ai']['advice'] ?? 'Thời tiết ổn định 🌤️');

// Đọc mảng dữ liệu trả về từ mô hình học máy Python
$hourly_data = $data['ai']['hourly'] ?? [];
$weekly_data = $data['ai']['weekly'] ?? [];
$monthly_data = $data['ai']['monthly'] ?? [];

// Tạo mảng dữ liệu dự phòng (Fallback) phòng khi cURL lỗi không làm vỡ giao diện
if (empty($hourly_data)) {
    for ($i = 0; $i < 24; $i += 2) {
        $hourly_data[] = ["time" => sprintf("%02d:00", (intval(date("H")) + $i) % 24), "temp" => floatval($temperature) + rand(-2, 2)];
    }
}
if (empty($monthly_data)) {
    for ($i = 1; $i <= 30; $i++) {
        $monthly_data[] = ["date" => date("d/m", strtotime("+$i days")), "temp" => floatval($temperature) + rand(-3, 3)];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkyCast Pro - Dashboard</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/Weather_WebApp/public/css/style.css">

    <!-- THƯ VIỆN ĐỒ THỊ CHART.JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .app-container { display: flex; min-height: 100vh; background: #eef2f7; }
        .main-content { flex: 1; padding: 40px; overflow-y: auto; }
        
        .content-header { margin-bottom: 25px; }
        .content-header h1 { font-size: 28px; color: #1e293b; font-weight: 700; }
        .content-header p { color: #64748b; font-size: 14px; }

        .bento-grid-row-1 { display: grid; grid-template-columns: 4fr 6fr; gap: 25px; margin-bottom: 25px; }
        .bento-grid-row-2 { display: grid; grid-template-columns: 4fr 6fr; gap: 25px; }

        .bento-card { background: #ffffff; border-radius: 24px; padding: 30px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03); border: 1px solid rgba(226, 232, 240, 0.8); }
        .bento-card h3 { font-size: 16px; font-weight: 600; color: #1e293b; margin-bottom: 20px; }

        .badge-ai { padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
        .badge-success { background: #e2f5ea; color: #15803d; }
        .badge-danger { background: #ffebee; color: #c62828; }
        
        .main-temp { font-size: 56px; font-weight: 700; color: #0f172a; margin: 15px 0 5px 0; }
        .weather-desc { font-size: 15px; color: #475569; margin-bottom: 20px; font-weight: 500; }
        .sub-metrics { display: flex; gap: 15px; background: #f8fafc; padding: 15px; border-radius: 16px; justify-content: space-between; font-size: 13px; color: #64748b; }

        .weekly-list { display: flex; flex-direction: column; gap: 14px; }
        .weekly-item { display: flex; justify-content: space-between; align-items: center; font-size: 14px; color: #334155; padding: 4px 0; border-bottom: 1px dashed #f1f5f9; }
        .weekly-item:last-child { border-bottom: none; }

        /* FLOATING POP-UP CHAT AI BÓNG BẨY */
        .ai-chat-bubble {
            position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; border-radius: 50%;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;
            display: flex; justify-content: center; align-items: center; font-size: 24px; cursor: pointer;
            box-shadow: 0 10px 25px rgba(79, 172, 254, 0.4); transition: 0.3s ease; z-index: 999;
        }
        .ai-chat-bubble:hover { transform: scale(1.08); }

        .ai-chat-window {
            position: fixed; bottom: 105px; right: 30px; width: 380px; height: 480px; border-radius: 24px;
            background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(15px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1); display: none; flex-direction: column; overflow: hidden;
            border: 1px solid rgba(255,255,255,0.5); z-index: 999; animation: popWindow 0.25s ease;
        }
        @keyframes popWindow { from { transform: translateY(15px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .chat-header { background: linear-gradient(to right, #4facfe, #00f2fe); color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .chat-messages { flex: 1; padding: 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; font-size: 13px; }
        .msg-bubble { max-width: 80%; padding: 10px 14px; border-radius: 16px; line-height: 1.4; }
        .msg-system { background: #eef2f5; color: #1e293b; align-self: flex-start; border-top-left-radius: 4px; }
        .msg-user { background: #4facfe; color: white; align-self: flex-end; border-top-right-radius: 4px; }
        
        .chat-input-area { padding: 12px; background: #fff; border-top: 1px solid #eef2f5; display: flex; gap: 8px; }
        .chat-input-area input { flex: 1; padding: 10px 14px; border: 1px solid #eef2f5; background: #f8fafc; border-radius: 20px; outline: none; font-size: 13px; }
        .chat-input-area button { width: 36px; height: 36px; border-radius: 50%; border: none; background: #4facfe; color: white; cursor: pointer; display: flex; justify-content: center; align-items: center; }
    </style>
</head>
<body>

    <div class="app-container">
        <?php if (file_exists("../app/views/layouts/sidebar.php")) { require_once "../app/views/layouts/sidebar.php"; } ?>

        <main class="main-content">
            <header class="content-header">
                <h1>Xin chào, <?= $username; ?>!</h1>
                <p>Dự báo thời tiết thông minh.</p>
            </header>

            <?php if (isset($data['ai']['success']) && $data['ai']['success'] === false): ?>
                <div style="background-color: #ffebee; color: #c62828; padding: 12px 20px; border-radius: 14px; margin-bottom: 20px; border: 1px solid #ef9a9a; font-size: 13px;">
                    <i class="fa-solid fa-circle-exclamation"></i> <strong>Lưu ý:</strong> Kết nối dữ liệu máy học tạm thời gián đoạn.
                </div>
            <?php endif; ?>

            <!-- HÀNG 1: THỜI TIẾT HIỆN TẠI & DIỄN BIẾN 24 GIỜ -->
            <div class="bento-grid-row-1">
                <div class="bento-card">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <span style="font-weight: 600; color: #475569; font-size: 14px;"><i class="fa-solid fa-location-dot"></i> Thành phố ID: <?= $city; ?></span>
                        <span class="badge-ai <?= $ai_badge_class; ?>"><?= $ai_badge_text; ?></span>
                    </div>
                    <div class="main-temp"><?= $temperature; ?>°C</div>
                    <div class="weather-desc"><?= $bridge_desc; ?></div>
                    
                    <div class="sub-metrics">
                        <span><i class="fa-solid fa-droplet"></i> <?= $humidity; ?>%</span>
                        <span><i class="fa-solid fa-wind"></i> <?= $wind_speed; ?> m/s</span>
                        <span><i class="fa-solid fa-cloud-showers-heavy"></i> <?= $precipitation; ?> mm</span>
                    </div>
                </div>

                <div class="bento-card">
                    <h3>Diễn biến 24 giờ tới</h3>
                    <div style="width: 100%; height: 160px; position: relative;">
                        <canvas id="hourlyChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- HÀNG 2: DỰ BÁO 7 NGÀY & XU HƯỚNG 30 NGÀY -->
            <div class="bento-grid-row-2">
                <div class="bento-card">
                    <h3>Dự báo 7 ngày</h3>
                    <div class="weekly-list">
                        <?php if (!empty($weekly_data)): ?>
                            <?php foreach ($weekly_data as $w): ?>
                                <div class="weekly-item">
                                    <span><?= htmlspecialchars($w['date']); ?></span>
                                    <i class="fa-solid fa-sun"></i>
                                    <strong><?= htmlspecialchars($w['data']['temperature'] ?? $temperature); ?>°C</strong>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php for($i=0; $i<7; $i++): ?>
                                <div class="weekly-item">
                                    <span><?= date("d/m", strtotime("+$i days")); ?></span>
                                    <i class="fa-solid fa-sun"></i>
                                    <strong><?= floatval($temperature) + rand(-1,2); ?>°C</strong>
                                </div>
                            <?php endfor; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bento-card">
                    <h3>Xu hướng nhiệt độ 30 ngày tới</h3>
                    <div style="width: 100%; height: 260px; position: relative;">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- FLOATING POP-UP CHAT BUBBLE -->
    <div class="ai-chat-bubble" onclick="toggleChatWindow()">
        <i class="fa-solid fa-comment-dots"></i>
    </div>

    <div class="ai-chat-window" id="chatWindow">
        <div class="chat-header">
            <span style="font-weight: 600;"><i class="fa-solid fa-brain"></i> Trợ lý ảo SkyCast AI</span>
            <span style="cursor: pointer;" onclick="toggleChatWindow()"><i class="fa-solid fa-xmark"></i></span>
        </div>
        <div class="chat-messages" id="chatMessages">
            <div class="msg-bubble msg-system" id="gemini-init-advice">
                <em>Đang phân tích thời tiết và gợi ý phối đồ từ Gemini AI... 🌤️</em>
            </div>
        </div>
        <div class="chat-input-area">
            <input type="text" id="userInput" placeholder="Nhập tin nhắn..." onkeypress="if(event.key==='Enter') sendChatMessage()">
            <button onclick="sendChatMessage()"><i class="fa-solid fa-paper-plane"></i></button>
        </div>
    </div>
<!-- Custom Logout Modal  -->
<div id="logoutModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(4px); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: #ffffff; padding: 30px; border-radius: 24px; width: 360px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); border: 1px solid rgba(226, 232, 240, 0.8); text-align: center; animation: popUp 0.25s ease;">
        <div style="width: 60px; height: 60px; background: #ffebee; color: #c62828; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 20px auto; font-size: 24px;">
            <i class="fa-solid fa-right-from-bracket"></i>
        </div>
        <h3 style="font-size: 18px; font-weight: 600; color: #1e293b; margin-bottom: 10px;">Đăng xuất?</h3>
        <p style="font-size: 14px; color: #64748b; margin-bottom: 25px; line-height: 1.5;">Bạn có chắc chắn muốn đăng xuất khỏi hệ thống SkyCast?</p>
        
        <div style="display: flex; gap: 12px; justify-content: center;">
            <button onclick="closeLogoutModal()" style="flex: 1; padding: 12px; border-radius: 14px; border: 1px solid #e2e8f0; background: #f8fafc; color: #64748b; font-weight: 500; cursor: pointer; font-size: 14px;">Hủy</button>
            <a href="/Weather_WebApp/auth/logout" style="flex: 1; padding: 12px; border-radius: 14px; border: none; background: linear-gradient(135deg, #ef4444, #dc2626); color: white; font-weight: 500; text-decoration: none; display: inline-block; font-size: 14px; line-height: 1.5;">Đăng xuất</a>
        </div>
    </div>
</div>

<style>
@keyframes popUp { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
</style>
    <script>
        const hourlyRaw = <?= json_encode($hourly_data); ?>;
        const monthlyRaw = <?= json_encode($monthly_data); ?>;

        // 1. VẼ BIỂU ĐỒ 24 GIỜ
        new Chart(document.getElementById('hourlyChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: hourlyRaw.map(item => item.time),
                datasets: [{ label: 'Nhiệt độ (°C)', data: hourlyRaw.map(item => item.temp), borderColor: '#f97316', borderWidth: 3, pointRadius: 1, tension: 0.3, fill: false }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { grid: { display: false } }, x: { grid: { display: false } } } }
        });

        // 2. VẼ BIỂU ĐỒ 30 NGÀY
        new Chart(document.getElementById('monthlyChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: monthlyRaw.map(item => item.date),
                datasets: [{ label: 'Nhiệt độ trung bình', data: monthlyRaw.map(item => item.temp), borderColor: '#38bdf8', backgroundColor: 'rgba(56, 189, 248, 0.05)', borderWidth: 2, pointRadius: 2, tension: 0.4, fill: true }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
        });

        function toggleChatWindow() {
            const w = document.getElementById('chatWindow');
            w.style.display = (w.style.display === 'flex') ? 'none' : 'flex';
        }

        // 3. GỌI BẤT ĐỒNG BỘ NẠP LỜI KHUYÊN PHỐI ĐỒ 
        window.addEventListener('DOMContentLoaded', () => {
            fetch('http://127.0.0.1:5000/chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message: "Hãy đưa ra nhận định tổng quan cực ngắn về thời tiết hiện tại và lời khuyên phối đồ phù hợp ngày hôm nay.",
                    context: { temp: '<?= $temperature; ?>', hum: '<?= $humidity; ?>', wind: '<?= $wind_speed; ?>', desc: '<?= $bridge_desc; ?>' }
                })
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('gemini-init-advice').innerHTML = `<strong>Nhận định từ Gemini 2.5 Flash:</strong><br>${data.response.replace(/\n/g, '<br>')}`;
            })
            .catch(() => {
                document.getElementById('gemini-init-advice').innerHTML = `<strong>Nhận định từ SkyCast AI:</strong><br><?= $ai_advice; ?>`;
            });
        });

        function sendChatMessage() {
            const input = document.getElementById('userInput');
            const txt = input.value.trim();
            if(!txt) return;

            const container = document.getElementById('chatMessages');
            const uB = document.createElement('div');
            uB.className = 'msg-bubble msg-user';
            uB.innerText = txt;
            container.appendChild(uB);
            input.value = '';
            container.scrollTop = container.scrollHeight;

            const lB = document.createElement('div');
            lB.className = 'msg-bubble msg-system';
            lB.id = 'ai-load';
            lB.innerText = 'AI đang phản hồi...';
            container.appendChild(lB);
            container.scrollTop = container.scrollHeight;

            fetch('http://127.0.0.1:5000/chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message: txt,
                    context: { temp: '<?= $temperature; ?>', hum: '<?= $humidity; ?>', wind: '<?= $wind_speed; ?>', desc: '<?= $bridge_desc; ?>' }
                })
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('ai-load').remove();
                const aiB = document.createElement('div');
                aiB.className = 'msg-bubble msg-system';
                aiB.innerHTML = data.response.replace(/\n/g, '<br>');
                container.appendChild(aiB);
                container.scrollTop = container.scrollHeight;
            })
            .catch(() => {
                document.getElementById('ai-load').remove();
                const eB = document.createElement('div');
                eB.className = 'msg-bubble msg-system';
                eB.style.color = '#c62828';
                eB.innerText = '❌ Lỗi kết nối AI Server.';
                container.appendChild(eB);
            });
        }
        
    </script>
</body>
</html>