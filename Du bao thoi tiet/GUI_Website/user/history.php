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
                <li class="menu-item active"><i class="fa-solid fa-calendar-days"></i> Lịch sử</li>
                <li class="menu-item" onclick="window.location.href='setting.php'"><i class="fa-solid fa-gear"></i> Cài đặt</li>
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