<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkyCast Pro - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/style.css"> <!-- Sử dụng CSS dùng chung[cite: 4] -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <div class="dashboard-container">
        <!-- Sidebar Menu dành riêng cho Admin[cite: 1] -->
        <nav class="sidebar">
            <div class="logo"><i class="fa-solid fa-cloud-bolt"></i> SkyCast</div>
            <ul class="menu-list">
                <li class="menu-item active"><i class="fa-solid fa-chart-pie"></i> Manager</li>
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
                    <div class="avatar"><img src="https://ui-avatars.com/api/?name=Admin&background=e74c3c&color=fff" alt="Admin"></div>
                </div>
            </header>
            
            <div style="margin-top: 40px;">
                <h2 style="margin-bottom: 20px; color: #2c3e50;">🛠️ Admin Control Panel (MLOps)</h2>

                <div style="display: flex; gap: 20px;">
                    <!-- Thẻ Dataset[cite: 1] -->
                    <div class="card" style="flex: 1;">
                        <div class="card-header">
                            <span class="card-title"><i class="fa-solid fa-database"></i> Dữ liệu huấn luyện (Dataset)</span>
                            <span class="view-all" onclick="loadFiles()">Làm mới</span>
                        </div>
                        <div id="file-list-container" style="max-height: 150px; overflow-y: auto;">
                            <p>Đang tải danh sách file...</p>
                        </div>
                        <p style="margin-top: 10px; font-size: 12px; color: #7f8c8d;">
                            *Copy file .csv mới vào thư mục /data của AI Server để nhận.[cite: 1]
                        </p>
                    </div>

                    <!-- Thẻ Retrain Model AI[cite: 1] -->
                    <div class="card" style="flex: 1; background: linear-gradient(135deg, #2c3e50 0%, #000000 100%); color: white;">
                        <div class="card-header">
                            <span class="card-title" style="color: white;"><i class="fa-solid fa-brain"></i> Trạng thái Model AI</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <p style="font-size: 14px; opacity: 0.8;">Độ chính xác hiện tại:</p>
                                <h1 id="acc-display" style="font-size: 36px;">--%</h1>
                                <p id="last-update" style="font-size: 12px; opacity: 0.6;">Chưa huấn luyện lại</p>
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
        document.addEventListener("DOMContentLoaded", function() {
            // Lấy danh sách file CSV huấn luyện[cite: 1]
            loadFiles();
            // Lấy lời khuyên nhanh cho admin để check trạng thái server[cite: 1]
            fetchAdminAdvice();
        });

        async function fetchAdminAdvice() {
            try {
                let fakeSensorData = { "temperature": 32, "humidity": 80, "wind_speed": 4, "precipitation": 0 };
                let res = await fetch('http://127.0.0.1:5000/advice', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(fakeSensorData)
                });
                let data = await res.json();
                let textElement = document.getElementById('ai-advice');
                textElement.innerText = "Trạng thái AI: " + data.message;
                textElement.style.color = (data.status_code == 1) ? "#e74c3c" : "#27ae60";
            } catch (err) {
                document.getElementById('ai-advice').innerText = "Lỗi kết nối Server Python!";
            }
        }

        async function loadFiles() {
            try {
                let res = await fetch('http://127.0.0.1:5000/admin/files');
                let data = await res.json();
                let html = `<ul style="list-style: none;">`;
                data.files.forEach(file => {
                    html += `<li style="padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.1); font-size: 14px;">
                                <i class="fa-solid fa-file-csv" style="color: #27ae60;"></i> ${file}
                            </li>`;
                });
                html += `</ul>`;
                document.getElementById('file-list-container').innerHTML = html;
            } catch (err) {
                document.getElementById('file-list-container').innerHTML = "<p>Lỗi tải danh sách file.</p>";
            }
        }

        async function triggerRetrain() {
            // Sử dụng SweetAlert2 đồng bộ thay cho hàm confirm thô cũ[cite: 1]
            Swal.fire({
                title: 'Huấn luyện lại hệ thống?',
                text: "Quá trình này sẽ gọi script huấn luyện và nạp lại mô hình máy học!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#7f8c8d',
                confirmButtonText: 'Bắt đầu huấn luyện!',
                cancelButtonText: 'Hủy bỏ'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    let btn = document.querySelector("button[onclick='triggerRetrain()']");
                    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang học...';
                    btn.style.background = "#95a5a6";

                    try {
                        let res = await fetch('http://127.0.0.1:5000/admin/retrain', { method: 'POST' });
                        let data = await res.json();

                        if (data.status === 'success') {
                            Swal.fire('Thành công!', data.message + '\nCác mô hình đã được nạp lại.', 'success');
                            document.getElementById('acc-display').innerText = "100%"; // Trạng thái OK từ Flask trả về
                            document.getElementById('last-update').innerText = "Cập nhật: Vừa xong";
                        } else {
                            Swal.fire('Lỗi Script!', data.message, 'error');
                        }
                    } catch (err) {
                        Swal.fire('Lỗi kết nối!', 'Không thể gửi yêu cầu retrain.', 'error');
                    } finally {
                        btn.innerHTML = '<i class="fa-solid fa-rotate"></i> RETRAIN NGAY';
                        btn.style.background = "#e74c3c";
                    }
                }
            });
        }

        function confirmLogout() {
            Swal.fire({
                title: 'Đăng xuất?',
                text: "Bạn có chắc muốn thoát phiên làm việc của Admin?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Đăng xuất ngay',
                cancelButtonText: 'Ở lại'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/auth/logout';
                }
            })
        }
    </script>
</body>
</html>