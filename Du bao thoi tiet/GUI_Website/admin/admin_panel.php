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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 <link rel="stylesheet" href="../public/css/style.css">
</head>

<body>

    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="logo"><i class="fa-solid fa-cloud-bolt"></i> SkyCast</div>
            <ul class="menu-list">
                <li class="menu-item active"><i class="fa-solid fa-chart-pie"></i>Manager</li>
                <li class="menu-item" onclick="confirmLogout()">
                    <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                </li>
            </ul>
        </nav>

        <main class="main-content">
            <header class="header">
                <div>
                    <h1>Xin chào, Admin!</h1>
                    <p id="ai-advice">Đang kết nối với AI...</p>
                </div>
                <div class="admin-profile">
                    <div class="avatar"><img src="../public/images/admin.webp" alt="Admin"></div>
                </div>
            </header>
            <div style="margin-top: 40px;">
                <h2 style="margin-bottom: 20px; color: #2c3e50;">🛠️ Admin Control Panel (MLOps)</h2>

                <div style="display: flex; gap: 20px;">
                    <div class="card" style="flex: 1;">
                        <div class="card-header">
                            <span class="card-title"><i class="fa-solid fa-database"></i> Dữ liệu huấn luyện (Dataset)</span>
                            <span class="view-all" onclick="loadFiles()">Làm mới</span>
                        </div>
                        <div id="file-list-container" style="max-height: 150px; overflow-y: auto;">
                            <p>Đang tải danh sách file...</p>
                        </div>
                        <p style="margin-top: 10px; font-size: 12px; color: #7f8c8d;">
                            *Copy file .csv mới vào thư mục /data để hệ thống tự nhận.
                        </p>
                    </div>

                    <div class="card" style="flex: 1; background: linear-gradient(135deg, #2c3e50 0%, #000000 100%); color: white;">
                        <div class="card-header">
                            <span class="card-title" style="color: white;"><i class="fa-solid fa-brain"></i> Trạng thái Model AI</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <p style="font-size: 14px; opacity: 0.8;">Độ chính xác hiện tại:</p>
                                <h1 id="acc-display" style="font-size: 36px;">--%</h1>
                                <p id="last-update" style="font-size: 12px; opacity: 0.6;">Chưa train lại</p>
                            </div>
                            <button onclick="triggerRetrain()" style="padding: 15px 30px; border-radius: 50px; border: none; background: #e74c3c; color: white; font-weight: bold; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 20px rgba(231, 76, 60, 0.3);">
                                <i class="fa-solid fa-rotate"></i> RETRAIN NGAY
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script>
        // Khi web chạy lên thì gọi hết các API cần thiết
        document.addEventListener("DOMContentLoaded", function() {
            // 1. Gọi API lấy lời khuyên
            fetchAdvice();
            // 2. Gọi API dự báo nhiệt độ
            fetchTemperature();
            // 3. Load danh sách file CSV cho admin
            loadFiles();
        });

        async function fetchAdvice() {
            try {
                // Dữ liệu giả lập
                let fakeSensorData = {
                    "temperature": 32,
                    "humidity": 80,
                    "wind_speed": 4,
                    "precipitation": 0
                };

                let res = await fetch('http://127.0.0.1:5000/advice', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(fakeSensorData)
                });

                let data = await res.json();
                let textElement = document.getElementById('ai-advice');
                textElement.innerText = data.message;
                textElement.style.color = (data.status_code == 1) ? "#e74c3c" : "#27ae60";

            } catch (err) {
                console.log(err);
                document.getElementById('ai-advice').innerText = "Lỗi kết nối Server Python!";
            }
        }

        async function fetchTemperature() {
            try {
                let inputData = {
                    "humidity": 65,
                    "wind_speed": 12,
                    "precipitation": 0,
                    "hour": new Date().getHours(),
                    "month": new Date().getMonth() + 1
                };

                let res = await fetch('http://127.0.0.1:5000/predict-temp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(inputData)
                });

                let data = await res.json();
                document.getElementById('temp-display').innerText = data.temperature_predicted + "°C";

            } catch (err) {
                console.log(err);
            }
        }

        async function loadFiles() {
            try {
                let res = await fetch('http://127.0.0.1:5000/admin/files');
                let data = await res.json();

                let html = `<ul style="list-style: none;">`;
                data.files.forEach(file => {
                    html += `<li style="padding: 8px 0; border-bottom: 1px solid rgba(0,0,0,0.1); font-size: 14px;">
                                <i class="fa-solid fa-file-csv" style="color: #27ae60;"></i> ${file}
                            </li>`;
                });
                html += `</ul>`;

                document.getElementById('file-list-container').innerHTML = html;
            } catch (err) {
                console.log(err);
            }
        }

        async function triggerRetrain() {
            if (!confirm("⚠️ Bạn có chắc muốn huấn luyện lại hệ thống? Việc này sẽ tốn khoảng vài giây.")) return;

            let btn = document.querySelector("button[onclick='triggerRetrain()']");
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang học...';
            btn.style.background = "#95a5a6";

            try {
                let res = await fetch('http://127.0.0.1:5000/admin/retrain', {
                    method: 'POST'
                });
                let data = await res.json();

                if (data.status === 'success') {
                    alert(data.message + "\n\n📊 Độ chính xác mới: " + data.metrics.log_acc + "%\n📉 Sai số nhiệt độ: " + data.metrics.rf_rmse);
                    document.getElementById('acc-display').innerText = data.metrics.log_acc + "%";
                    document.getElementById('last-update').innerText = "Cập nhật: Vừa xong";

                    // Tải lại web để nhận model mới
                    location.reload();
                } else {
                    alert("Lỗi: " + data.message);
                }
            } catch (err) {
                alert("Lỗi kết nối Server!");
            } finally {
                btn.innerHTML = '<i class="fa-solid fa-rotate"></i> RETRAIN NGAY';
                btn.style.background = "#e74c3c";
            }
        }
//logout
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