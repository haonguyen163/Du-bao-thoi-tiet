<?php
class AuthController extends Controller {

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Xử lý Route đăng nhập: domain/auth/login
    public function login() {
        $error = "";
        $email = "";

        if (isset($_POST['login'])) {
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            if (empty($email) || empty($password)) {
                $error = "Vui lòng nhập Email và Mật khẩu!";
            } else {
                $userModel = $this->model("UserModel");
                $user = $userModel->getUserByEmail($email);

                if ($user) {
                    if ($password === $user['password']) { // So sánh mật khẩu thuần từ DB cũ
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['email'] = $user['email'];

                        // 🛠️ ĐÃ SỬA: Chuyển hướng chuẩn xác theo các action của DashboardController
                        if ($user['role'] == 'admin') {
                            header("Location: /Weather_WebApp/dashboard/admin");
                            exit();
                        }
                        header("Location: /Weather_WebApp/dashboard/index"); // Trang chủ User
                        exit();
                    } else {
                        $error = "Mật khẩu không chính xác!";
                    }
                } else {
                    $error = "Email này chưa được đăng ký!";
                }
            }
        }

        // Đổ view đăng nhập kèm biến dữ liệu lỗi (nếu có)
        $this->view("auth/login", [
            'error' => $error,
            'email' => $email
        ]);
    }

    // Xử lý Route đăng ký: domain/auth/register
    public function register() {
        $error = "";
        $success = "";
        $username = "";
        $email = "";

        if (isset($_POST['register'])) {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $re_password = $_POST['re_password'];

            if (empty($username) || empty($email) || empty($password) || empty($re_password)) {
                $error = "Vui lòng nhập đầy đủ thông tin!";
            } elseif ($password !== $re_password) {
                $error = "Mật khẩu nhập lại không khớp!";
            } else {
                $userModel = $this->model("UserModel");
                
                if ($userModel->checkUserExist($username, $email)) {
                    $error = "Tên đăng nhập hoặc Email đã được sử dụng!";
                } else {
                    $result = $userModel->registerUser($username, $email, $password);
                    if ($result) {
                        $success = "Đăng ký thành công! Bạn có thể đăng nhập ngay.";
                        // Xóa trống dữ liệu cũ khi thành công để form sạch sẽ
                        $username = ""; 
                        $email = "";
                    } else {
                        $error = "Lỗi hệ thống, vui lòng thử lại sau!";
                    }
                }
            }
        }

        // Đổ dữ liệu ra View bao gồm cả data cũ để giữ lại input khi lỗi
        $this->view("auth/register", [
            'error' => $error,
            'success' => $success,
            'username' => $username,
            'email' => $email
        ]);
    }

    // Xử lý Route đăng xuất: domain/auth/logout
    public function logout() {
        session_unset(); 
        session_destroy(); 
        header("Location: /Weather_WebApp/auth/login");
        exit();
    }
}