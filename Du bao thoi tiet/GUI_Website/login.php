<?php
session_start();
require_once 'manager/database.php';
$db = new Database();
$error = "";
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    if (empty($email) || empty($password)) {
        $error = "Vui lòng nhập Email và Mật khẩu!";
    } else {
        $sql = "SELECT * FROM data_user WHERE email = ?";
        $users = $db->select($sql, 's', [$email]);
        if (!empty($users)) {
            $user = $users[0];
            if ($password==$user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                if ($user['role'] == 'admin') {
                    header("Location: admin/admin_panel.php"); 
                exit();
                }
                header("Location: user/home.php"); 
                exit();
            } else {
                $error = "Mật khẩu không chính xác!";
            }
        } else {
            $error = "Email này chưa được đăng ký!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkyCast - Đăng Nhập</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* CSS GIỮ NGUYÊN */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: linear-gradient(120deg, #a1c4fd 0%, #c2e9fb 100%); padding: 20px; }
        .container-card { display: flex; flex-direction: row; width: 100%; max-width: 900px; background: rgba(255, 255, 255, 0.9); border-radius: 30px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1); overflow: hidden; min-height: 500px; }
        .brand-panel { flex: 0.8; padding: 40px; display: flex; flex-direction: column; justify-content: center; align-items: center; background: linear-gradient(180deg, #4facfe 0%, #00f2fe 100%); color: white; text-align: center; position: relative; }
        .brand-panel::before { content: ''; position: absolute; top: -50px; left: -50px; width: 150px; height: 150px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; }
        .brand-icon { font-size: 80px; margin-bottom: 20px; filter: drop-shadow(0 5px 15px rgba(0,0,0,0.1)); }
        .brand-title { font-size: 32px; font-weight: 700; margin-bottom: 10px; }
        .form-panel { flex: 1.2; padding: 50px; display: flex; flex-direction: column; justify-content: center; }
        .form-header { margin-bottom: 30px; }
        .form-header h2 { font-size: 28px; color: #2c3e50; font-weight: 700; margin-bottom: 5px; }
        .input-group { margin-bottom: 20px; position: relative; }
        .input-group label { display: block; font-size: 13px; color: #34495e; margin-bottom: 8px; font-weight: 600; }
        .input-group input { width: 100%; padding: 12px 15px; padding-left: 40px; border: 2px solid #eef2f5; border-radius: 12px; background: #f4f7f6; outline: none; transition: 0.3s; color: #2c3e50; }
        .input-group input:focus { border-color: #4facfe; background: #fff; }
        .input-group i { position: absolute; left: 15px; top: 38px; color: #a4b0be; font-size: 14px; }
        .btn-submit { width: 100%; padding: 15px; margin-top: 10px; border: none; border-radius: 12px; background: linear-gradient(to right, #4facfe, #00f2fe); color: white; font-size: 16px; font-weight: 600; cursor: pointer; transition: 0.3s; box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4); }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(79, 172, 254, 0.6); }
        .switch-link { margin-top: 25px; text-align: center; font-size: 14px; color: #7f8c8d; }
        .switch-link a { color: #4facfe; text-decoration: none; font-weight: 600; }
        .switch-link a:hover { text-decoration: underline; }
        .forgot-pass { text-align: right; margin-bottom: 20px; font-size: 13px; }
        .forgot-pass a { color: #7f8c8d; text-decoration: none; }
        .forgot-pass a:hover { color: #4facfe; }
        @media (max-width: 768px) { .container-card { flex-direction: column; } .brand-panel { padding: 30px; min-height: 150px; } .brand-icon { font-size: 50px; margin-bottom: 10px; } .brand-title { font-size: 24px; } }

        /* Style thông báo lỗi */
        .alert-error {
            background-color: #ffebee; color: #c62828; border: 1px solid #ef9a9a;
            padding: 10px; margin-bottom: 20px; border-radius: 10px; font-size: 14px; text-align: center;
        }
    </style>
</head>
<body>
    <div class="container-card">
        <div class="brand-panel">
            <div class="brand-icon">
                <i class="fa-solid fa-cloud-moon"></i> </div>
            <h1 class="brand-title">SkyCast</h1>
            <p>Chào mừng bạn quay trở lại!</p>
        </div>

        <div class="form-panel">
            <div class="form-header">
                <h2>Đăng nhập</h2>
                <p>Điền thông tin để truy cập dự báo.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST"> 
                <div class="input-group">
                    <label>Email</label>
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" placeholder="name@example.com" value="<?php echo isset($email) ? $email : ''; ?>">
                </div>

                <div class="input-group">
                    <label>Mật khẩu</label>
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" placeholder="••••••••">
                </div>

                <div class="forgot-pass">
                    <a href="forgot-password.php">Quên mật khẩu?</a>
                </div>

                <button type="submit" name="login" class="btn-submit">Đăng nhập</button>
            </form>

            <div class="switch-link">
                Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
            </div>
        </div>
    </div>

</body>
</html>