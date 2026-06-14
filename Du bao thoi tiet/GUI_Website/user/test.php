<?php
session_start();
// 1. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
} else {
    // Chỉ hiện alert nếu mới login xong (tùy chọn)
    // echo"<script>alert('Đăng Nhập Thành Công');</script>";
}
// =======================================================================
// 2. GỌI API PYTHON (AI PREDICTION)
// =======================================================================
// A. ĐỌC DỮ LIỆU TỪ FILE CSV (THAY VÌ NHẬP TAY)
$csv_file_path = 'C:\Users\hao09\Downloads\weather\weather-vn-4.csv'; // <-- SỬA ĐƯỜNG DẪN Ở ĐÂY
// Giá trị mặc định phòng khi không đọc được file
$current_weather = array(
    "temperature" => 30, "humidity" => 70, "wind_speed" => 5, 
    "precipitation" => 0, "hour" => 12, "month" => 6
);
if (file_exists($csv_file_path)) {
    // Đọc toàn bộ file vào một mảng (mỗi dòng là 1 phần tử)
    $lines = file($csv_file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!empty($lines)) {
        // Lấy dòng cuối cùng (Dữ liệu mới nhất)
        $last_line = end($lines);
        // Tách dòng đó ra thành mảng (phân cách bởi dấu phẩy)
        $data_csv = str_getcsv($last_line, ",");

        // --- QUAN TRỌNG: ÁNH XẠ DỮ LIỆU ---
        // Bạn phải sửa số [0], [1]... dưới đây cho khớp với file CSV của bạn
        // Giả sử cột 0 là thời gian dạng "2023-12-05 14:30:00"
        $timestamp = strtotime($data_csv[0]); 
        $current_weather = array(
            "city" => floatval($data_csv[2]),
            "province" => floatval($data_csv[1]),
            "temperature"   => floatval($data_csv[3]), // Cột 1 là Nhiệt độ
            "humidity"      => floatval($data_csv[6]), // Cột 2 là Độ ẩm
            "wind_speed"    => floatval($data_csv[11]), // Cột 3 là Gió
            "precipitation" => floatval($data_csv[9]), // Cột 4 là Mưa
            // Tự động tách Giờ và Tháng từ cột thời gian (Cột 0)
            "year" => date("y", $timestamp),

            "hour"          => date("H", $timestamp), 
            "month"         => date("m", $timestamp)
        );
    }
} else {
    // Nếu không thấy file thì báo lỗi nhẹ để biết
    $error_message = "Không tìm thấy file CSV tại: " . $csv_file_path;
}
// B. Cấu hình cURL gọi sang Python
$api_url = "http://127.0.0.1:5000/predict";
$prediction_data = null;
$error_message = "";

try {
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($current_weather));
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Đợi tối đa 5s

    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        $error_message = "Không kết nối được với AI Server (Python). Hãy kiểm tra xem file app_server.py đã chạy chưa?";
    } else {
        $prediction_data = json_decode($response, true);
    }
    curl_close($ch);
} catch (Exception $e) {
    $error_message = "Lỗi hệ thống: " . $e->getMessage();
}

// Hàm hỗ trợ chọn icon thời tiết
function getWeatherIcon($rain, $temp) {
    if ($rain > 0.5) return 'fa-cloud-showers-heavy';
    if ($temp > 30) return 'fa-sun';
    if ($temp < 15) return 'fa-snowflake';
    return 'fa-cloud-sun';
}
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
    
    <style>
        .ai-advice-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 10px;
        }
        .advice-good { background-color: #d4edda; color: #155724; }
        .advice-bad { background-color: #f8d7da; color: #721c24; }
        .error-box { color: red; padding: 10px; background: #fff0f0; border-radius: 5px; margin-bottom: 20px;}
    </style>
</head>

<body>

    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="logo">
                <i class="fa-solid fa-cloud-bolt"></i> SkyCast
            </div>
            <ul class="menu-list">
                <li class="menu-item active"><i class="fa-solid fa-chart-pie"></i> Tổng quan</li>
                <li class="menu-item" onclick="window.location.href='map.php'"><i class="fa-solid fa-location-dot"></i> Bản đồ</li>
                <li class="menu-item" onclick="window.location.href='history.php'"><i class="fa-solid fa-calendar-days"></i> Lịch sử</li>
                <li class="menu-item" onclick="window.location.href='setting.php'"><i class="fa-solid fa-gear"></i> Cài đặt</li>
                <li class="menu-item" onclick="confirmLogout()">
                    <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                </li>
            </ul>
        </nav>

        <main class="main-content">
            <header class="header">
                <div>
                    <h1>Xin chào, <?= $_SESSION['username'] ?? 'User' ?>!</h1>
                    <p>Chào mừng bạn quay trở lại. <?= $current_weather["year"]+2000 ;?></p>
                </div>
                <div class="user-profile">
                    <div class="search-bar">
                        <i class="fa-solid fa-magnifying-glass" style="color: #ccc;"></i>
                        <input type="text" placeholder="Tìm thành phố...">
                    </div>
                    <div class="avatar">
                        <img src="https://ui-avatars.com/api/?name=<?= $_SESSION['username'] ?? 'User' ?>&background=0D8ABC&color=fff" alt="User">
                    </div>
                </div>
            </header>

            <?php if($error_message): ?>
                <div class="error-box">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?= $error_message ?>
                </div>
            <?php endif; ?>

            <div class="bento-grid">

                <div class="card main-weather-card">
                    <div class="card-header">
                        <span class="card-title"><i class="fa-solid fa-location-dot"></i> <?= $current_weather["city"]; ?></span>
                        
                        <?php if(isset($prediction_data['success']) && $prediction_data['success']): ?>
                            <span class="ai-advice-badge <?= ($prediction_data['advisor_code'] == 0) ? 'advice-good' : 'advice-bad' ?>">
                               <!-- AI: <?= $prediction_data['advice'] ?>-->
                            </span>
                        <?php endif; ?>

                        <span class="view-all">Chi tiết</span>
                    </div>
                    <div class="temp-display">
                        <div class="big-temp"><?= $current_weather['temperature'] ?>°C</div>
                        <i class="fa-solid fa-sun big-icon"></i>
                    </div>
                    <div class="weather-meta">
                        <div class="meta-item"><i class="fa-solid fa-droplet"></i> <?= $current_weather['humidity'] ?>%</div>
                        <div class="meta-item"><i class="fa-solid fa-wind"></i> <?= $current_weather['wind_speed'] ?> km/h</div>
                    </div>
                </div>

                <div class="card chart-card">
                    <div class="card-header">
                        <span class="card-title">Nhiệt độ trong ngày</span>
                        <span class="view-all">Xem tất cả</span>
                    </div>
                    <div class="chart-bars">
                        <div class="bar-col"><div class="bar" style="height: 40px;"></div><span class="time-label">10AM</span></div>
                        <div class="bar-col"><div class="bar active" style="height: 70px;"></div><span class="time-label">12PM</span></div>
                        <div class="bar-col"><div class="bar" style="height: 80px;"></div><span class="time-label">2PM</span></div>
                        <div class="bar-col"><div class="bar" style="height: 60px;"></div><span class="time-label">4PM</span></div>
                        <div class="bar-col"><div class="bar" style="height: 50px;"></div><span class="time-label">6PM</span></div>
                        <div class="bar-col"><div class="bar" style="height: 30px;"></div><span class="time-label">8PM</span></div>
                    </div>
                </div>

                <div class="card forecast-list-card">
                    <div class="card-header">
                        <span class="card-title">Dự báo AI (<?= isset($prediction_data['forecast']) ? count($prediction_data['forecast']) : 0 ?> ngày tới)</span>
                    </div>

                    <?php if (isset($prediction_data['success']) && $prediction_data['success']): ?>
                        <?php foreach ($prediction_data['forecast'] as $day): ?>
                            <?php 
                                // Logic chọn màu icon
                                $temp = $day['stats']['temperature'];
                                $rain = $day['stats']['precipitation'];
                                $iconClass = getWeatherIcon($rain, $temp);
                                $color = ($temp > 30) ? 'orange' : '#0D8ABC';
                            ?>
                            <div class="forecast-row">
                                <span class="f-day"><?= $day['day'] ?></span> <div style="display:flex; align-items:center; gap:10px;">
                                    <?php if($rain > 0.5): ?>
                                        <span style="font-size: 0.8rem; color: #555;"><?= $rain ?>mm</span>
                                    <?php endif; ?>
                                    
                                    <i class="fa-solid <?= $iconClass ?> f-icon" style="color: <?= $color ?>;"></i>
                                </div>

                                <span class="f-temp"><?= $temp ?>°C</span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="padding: 20px; text-align: center; color: #999;">
                            Đang chờ dữ liệu AI...
                        </div>
                    <?php endif; ?>

                </div>

                <div class="card stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-eye"></i></div>
                    <div class="stat-value">10km</div>
                    <div class="stat-label">Tầm nhìn</div>
                </div>

                <div class="card stat-card">
                    <div class="stat-icon" style="color: #e74c3c;"><i class="fa-solid fa-sun"></i></div>
                    <div class="stat-value">High</div>
                    <div class="stat-label">UV Index</div>
                </div>

                <div class="card stat-card">
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
</body>
</html>