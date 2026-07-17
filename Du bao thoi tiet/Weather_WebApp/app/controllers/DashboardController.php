<?php
class DashboardController extends Controller {

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        // Bảo mật check session tập trung tại đây
        if (!isset($_SESSION['user_id'])) {
            header("Location: /Weather_WebApp/auth/login");
            exit();
        }
    }

    // Route mặc định: domain/dashboard/index hoặc domain/dashboard[cite: 2]
    public function index() {
        $weatherModel = $this->model("WeatherModel");
        
        // 1. Lấy dữ liệu thực tế từ file CSV hiện tại[cite: 2]
        $weatherInput = $weatherModel->getLatestWeatherInput();
        
        // 2. Thiết lập cơ chế Cache dữ liệu AI để mở trang siêu nhanh
        $cacheTime = 300; // Thời gian lưu dữ liệu đệm: 300 giây (5 phút)
        $currentTime = time();
        
        // Tạo một mã hash unique dựa trên dữ liệu CSV hiện tại để nhận biết nếu file CSV có dòng mới
        $weatherHash = md5(json_encode($weatherInput));

        // Kiểm tra xem đã có dữ liệu lưu tạm trong Session chưa, và còn trong thời hạn 5 phút không
        if (
            isset($_SESSION['cached_ai_result']) && 
            isset($_SESSION['cache_expires']) && 
            $_SESSION['cache_expires'] > $currentTime &&
            isset($_SESSION['cache_weather_hash']) &&
            $_SESSION['cache_weather_hash'] === $weatherHash
        ) {
            // Lấy luôn dữ liệu cũ ra xài, bỏ qua bước gọi cURL sang server Python giúp tải trang tức thì
            $aiResult = $_SESSION['cached_ai_result'];
        } else {
            // Nếu hết hạn hoặc dữ liệu CSV có sự thay đổi mới, gọi server Python để cập nhật[cite: 2]
            $aiResult = $weatherModel->getAiPredictions($weatherInput);
            
            // Nếu gọi server Python thành công và không bị lỗi kết nối, tiến hành lưu đệm vào Session
            if (isset($aiResult['success']) && $aiResult['success'] !== false) {
                $_SESSION['cached_ai_result'] = $aiResult;
                $_SESSION['cache_expires'] = $currentTime + $cacheTime;
                $_SESSION['cache_weather_hash'] = $weatherHash;
            }
        }

        // Đổ toàn bộ dữ liệu đã tối ưu tốc độ ra View[cite: 2]
        $this->view("dashboard/user", [
            "weather" => $weatherInput,
            "ai" => $aiResult
        ]);
    }

    // Route dành riêng cho Admin: domain/dashboard/admin[cite: 2]
    public function admin() {
        // Kiểm tra phân quyền admin[cite: 2]
        if (isset($_SESSION['username']) && $_SESSION['username'] !== 'Admin') {
            header("Location: /Weather_WebApp/dashboard/index");
            exit();
        }

        // Đổ ra giao diện admin control panel[cite: 1, 2]
        $this->view("dashboard/admin");
    }

    // Route Lịch sử: domain/dashboard/history
    public function history() {
        $this->view("dashboard/history");
    }

    // Route Cài đặt: domain/dashboard/setting
    public function setting() {
        $this->view("dashboard/setting");
    }
}