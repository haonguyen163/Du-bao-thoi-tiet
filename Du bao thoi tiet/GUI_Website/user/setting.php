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

<body>

    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="logo">
                <a href="home.php"><i class="fa-solid fa-cloud-bolt"></i> SkyCast</a> 
            </div>
            <ul class="menu-list">
                <li class="menu-item" onclick="window.location.href='home.php'"><i class="fa-solid fa-chart-pie"></i> Tổng quan</li>
                <li class="menu-item" onclick="window.location.href='map.php'"><i class="fa-solid fa-location-dot"></i> Bản đồ</li>
                <li class="menu-item" onclick="window.location.href='history.php'"><i class="fa-solid fa-calendar-days"></i> Lịch sử</li>
                <li class="menu-item active"><i class="fa-solid fa-gear"></i> Cài đặt</li>
                <li class="menu-item" onclick="confirmLogout()">
                    <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                </li>
            </ul>
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
                    <div class="card-header">
                        <span class="card-title"><i class="fa-solid fa-location-dot"></i> TP. Hồ Chí Minh</span>
                        <span class="view-all">Chi tiết</span>
                    </div>
                    <div class="temp-display">
                        <div class="big-temp">31°C</div>
                        <i class="fa-solid fa-sun big-icon"></i>
                    </div>
                    <div class="weather-meta">
                        <div class="meta-item"><i class="fa-solid fa-droplet"></i> 65%</div>
                        <div class="meta-item"><i class="fa-solid fa-wind"></i> 12 km/h</div>
                    </div>
                </div>

                <div class="card chart-card">
                    <div class="card-header">
                        <span class="card-title">Nhiệt độ trong ngày</span>
                        <span class="view-all">Xem tất cả</span>
                    </div>
                    <div class="chart-bars">
                        <div class="bar-col">
                            <div class="bar" style="height: 40px;"></div><span class="time-label">10AM</span>
                        </div>
                        <div class="bar-col">
                            <div class="bar active" style="height: 70px;"></div><span class="time-label">12PM</span>
                        </div>
                        <div class="bar-col">
                            <div class="bar" style="height: 80px;"></div><span class="time-label">2PM</span>
                        </div>
                        <div class="bar-col">
                            <div class="bar" style="height: 60px;"></div><span class="time-label">4PM</span>
                        </div>
                        <div class="bar-col">
                            <div class="bar" style="height: 50px;"></div><span class="time-label">6PM</span>
                        </div>
                        <div class="bar-col">
                            <div class="bar" style="height: 30px;"></div><span class="time-label">8PM</span>
                        </div>
                    </div>
                </div>

                <div class="card forecast-list-card">
                    <div class="card-header">
                        <span class="card-title">Dự báo 7 ngày</span>
                    </div>
                    <div class="forecast-row">
                        <span class="f-day">Thứ 6</span>
                        <i class="fa-solid fa-cloud-rain f-icon"></i>
                        <span class="f-temp">28°C</span>
                    </div>
                    <div class="forecast-row">
                        <span class="f-day">Thứ 7</span>
                        <i class="fa-solid fa-sun f-icon" style="color: orange;"></i>
                        <span class="f-temp">32°C</span>
                    </div>
                    <div class="forecast-row">
                        <span class="f-day">Chủ Nhật</span>
                        <i class="fa-solid fa-cloud f-icon"></i>
                        <span class="f-temp">30°C</span>
                    </div>
                    <div class="forecast-row">
                        <span class="f-day">Thứ 2</span>
                        <i class="fa-solid fa-bolt f-icon"></i>
                        <span class="f-temp">27°C</span>
                    </div>
                    <div class="forecast-row">
                        <span class="f-day">Thứ 3</span>
                        <i class="fa-solid fa-sun f-icon" style="color: orange;"></i>
                        <span class="f-temp">33°C</span>
                    </div>
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