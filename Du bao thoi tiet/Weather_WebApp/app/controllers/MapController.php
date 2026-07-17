<?php
class MapController extends Controller {

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Bảo mật tập trung: Nếu chưa đăng nhập thì đá về trang login
        if (!isset($_SESSION['user_id'])) {
            header("Location: /Weather_WebApp/auth/login");
            exit();
        }
    }

    // Route: domain/map/index hoặc domain/map
    public function index() {
        // Nạp view bản đồ nằm trong thư mục app/views/map/index.php
        $this->view("map/index");
    }
}