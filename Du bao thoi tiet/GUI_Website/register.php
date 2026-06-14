<?php
session_start();
require_once 'manager/database.php';
$db = new Database();
$error = "";
$success = "";
if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $re_password = $_POST['re_password'];
    if (empty($username)&&empty($email)&&empty($password)&&empty($re_password)) {
        $error = "Vui lòng nhập đầy đủ thông tin!";
    } elseif ($password !== $re_password) {
        $error = "Mật khẩu nhập lại không khớp!";
    } else {
        $checkSQL = "SELECT id FROM data_user WHERE username = ? OR email = ?";
        $checkExist = $db->select($checkSQL, 'ss', [$username, $email]);
        if (!empty($checkExist)) {
            $error = "Tên đăng nhập hoặc Email đã được sử dụng!";
        } else {
            $sql = "INSERT INTO data_user (username, email, password) VALUES (?, ?, ?)";
            $result = $db->execute($sql, 'sss', [$username, $email, $hashed_password]);
            if ($result) {
                $success = "Đăng ký thành công! Bạn có thể đăng nhập ngay.";
            } else {
                $error = "Lỗi hệ thống, vui lòng thử lại sau!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkyCast - Đăng Ký Tài Khoản</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* GIỮ NGUYÊN CSS CŨ (Layout & Background) */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        
        body {
            display: flex; justify-content: center; align-items: center; min-height: 100vh;
            background: linear-gradient(120deg, #a1c4fd 0%, #c2e9fb 100%);
            padding: 20px;
        }

        .container-card {
            display: flex; flex-direction: row; width: 100%; max-width: 900px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            min-height: 550px; /* Tăng chiều cao chút cho form thoáng */
        }

        /* CỘT TRÁI - BRANDING (Giữ style xanh cũ) */
        .brand-panel {
            flex: 0.8; padding: 40px;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            background: linear-gradient(180deg, #4facfe 0%, #00f2fe 100%);
            color: white; text-align: center; position: relative;
        }
        
        /* Vòng tròn trang trí */
        .brand-panel::before {
            content: ''; position: absolute; top: -50px; left: -50px;
            width: 150px; height: 150px; background: rgba(255, 255, 255, 0.2); border-radius: 50%;
        }

        .brand-icon { font-size: 80px; margin-bottom: 20px; filter: drop-shadow(0 5px 15px rgba(0,0,0,0.1)); }
        .brand-title { font-size: 32px; font-weight: 700; margin-bottom: 10px; }
        .brand-desc { font-size: 16px; opacity: 0.9; max-width: 250px; line-height: 1.5; }

        /* CỘT PHẢI - FORM ĐĂNG KÝ (Phần mới) */
        .form-panel {
            flex: 1.2; padding: 50px;
            display: flex; flex-direction: column; justify-content: center;
        }

        .form-header { margin-bottom: 30px; }
        .form-header h2 { font-size: 28px; color: #2c3e50; font-weight: 700; margin-bottom: 5px; }
        .form-header p { font-size: 14px; color: #7f8c8d; }

        /* Style cho Input */
        .input-group { margin-bottom: 20px; position: relative; }
        
        .input-group label {
            display: block; font-size: 13px; color: #34495e; margin-bottom: 8px; font-weight: 600;
        }

        .input-group input {
            width: 100%; padding: 12px 15px; padding-left: 40px; /* Chừa chỗ cho icon */
            border: 2px solid #eef2f5; border-radius: 12px;
            background: #f4f7f6; outline: none; transition: 0.3s; color: #2c3e50;
        }

        .input-group input:focus { border-color: #4facfe; background: #fff; }

        /* Icon trong input */
        .input-group i {
            position: absolute; left: 15px; top: 38px; color: #a4b0be; font-size: 14px;
        }

        /* Nút Đăng Ký */
        .btn-submit {
            width: 100%; padding: 15px; margin-top: 10px;
            border: none; border-radius: 12px;
            background: linear-gradient(to right, #4facfe, #00f2fe);
            color: white; font-size: 16px; font-weight: 600;
            cursor: pointer; transition: 0.3s;
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
        }

        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(79, 172, 254, 0.6); }

        /* Link chuyển trang */
        .switch-link { margin-top: 25px; text-align: center; font-size: 14px; color: #7f8c8d; }
        .switch-link a { color: #4facfe; text-decoration: none; font-weight: 600; }
        .switch-link a:hover { text-decoration: underline; }

        /* Responsive */
        @media (max-width: 768px) {
            .container-card { flex-direction: column; }
            .brand-panel { padding: 30px; min-height: 200px; }
            .form-panel { padding: 30px; }
        }
    </style>
</head>
<body>

    <div class="container-card">
        <div class="brand-panel">
            <div class="brand-icon">
                <i class="fa-solid fa-cloud-sun-rain"></i>
            </div>
            <h1 class="brand-title">SkyCast</h1>
            <p class="brand-desc">Dự báo thời tiết chính xác từng phút cho hành trình của bạn.</p>
        </div>

        <div class="form-panel">
            <div class="form-header">
                <h2>Tạo tài khoản</h2>
                <p>Nhập thông tin bên dưới để bắt đầu nhé!</p>
            </div>

            <form action="#" method="POST">
                <div class="input-group">
                    <label>Tên hiển thị</label>
                    <i class="fa-solid fa-user"></i>
                    <input type="text" placeholder="Ví dụ: Nguyen Van A" name="username">
                </div>

                <div class="input-group">
                    <label>Email</label>
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" placeholder="name@example.com" name="email">
                </div>

                <div class="input-group">
                    <label>Mật khẩu</label>
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" placeholder="••••••••" name="password">
                </div>

                <div class="input-group">
                    <label>Nhập Lại Mật khẩu</label>
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" placeholder="••••••••" name="re_password">
                </div>

                <button type="submit" class="btn-submit" name="register">Đăng Ký Ngay</button>
            </form>

            <div class="switch-link">
                Đã có tài khoản? <a href="login.php">Đăng nhập</a>
            </div>
        </div>
    </div>

</body>
</html>