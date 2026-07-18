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
    // Hàm thực thi cURL lõi được tối ưu hóa
    private function executeCurl($url, $payload) {
        try {
            $ch = curl_init($url); //
            
            // Cấu hình payload dữ liệu JSON
            $jsonData = json_encode($payload); 
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
            curl_setopt($ch, CURLOPT_POST, 1); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            
          
            curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
        
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            
            // Cấu hình Headers và User-Agent chuẩn để Flask không từ chối request
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData),
                'Accept: application/json'
            ]); 
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) SkyCastPHP/1.0'); 

            $response = curl_exec($ch);
            
            // Kiểm tra lỗi hệ thống cURL (nếu có)
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
                curl_close($ch);
                return [
                    'success' => false, 
                    'error' => 'cURL dịch vụ lỗi (' . $url . '): ' . $error_msg
                ];
            }
            
            curl_close($ch); 

            if ($response) {
                $data = json_decode($response, true); 
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $data;
                }
                return ['success' => false, 'error' => 'Dữ liệu trả về từ Python không đúng định dạng JSON chuẩn!'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()]; 
        }
        return ['success' => false, 'error' => 'Server Python phản hồi trống (Empty Response)!'];
    }
}
