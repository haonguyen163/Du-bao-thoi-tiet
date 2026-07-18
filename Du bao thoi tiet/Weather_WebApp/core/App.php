<?php
class App {
    protected $controller = "AuthController";
    protected $action = "login";
    protected $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        // 1. Kiểm tra và nạp Controller
        if (isset($url[0])) {
            $controllerName = ucfirst($url[0]) . "Controller";
            if (file_exists("../app/controllers/" . $controllerName . ".php")) {
                $this->controller = $controllerName;
                unset($url[0]);
                
                // Đổi action mặc định tương ứng với Controller
                if ($this->controller === "DashboardController" || $this->controller === "MapController") {
                    $this->action = "index";
                } else {
                    $this->action = "login";
                }
            }
        }

        require_once "../app/controllers/" . $this->controller . ".php";
        
        // Lưu lại tên Controller dưới dạng chuỗi để dùng cho logic kiểm tra phía dưới
        $currentControllerName = (string)$this->controller;
        
        // Khởi tạo Object
        $this->controller = new $this->controller;

        // 2. Kiểm tra Action (Hàm) trong Controller
        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->action = $url[1];
                unset($url[1]);
            }
        }

        // 3. Lấy các tham số còn lại trên URL
        $this->params = $url ? array_values($url) : [];

        // 4. Kiểm tra an toàn trước khi thực thi
        if (method_exists($this->controller, $this->action)) {
            call_user_func_array([$this->controller, $this->action], $this->params);
        } else {
            
            // Không gọi hàm get_class() lên Object nữa
            $isDashboard = ($currentControllerName === "DashboardController");
            $isMap = ($currentControllerName === "MapController");

            if ($isDashboard || $isMap) {
                $fallback = "index";
            } else {
                $fallback = "login";
            }

            if (method_exists($this->controller, $fallback)) {
                call_user_func_array([$this->controller, $fallback], $this->params);
            } else {
                die("Lỗi: Không tìm thấy phương thức hợp lệ.");
            }
        }
    }

    private function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(trim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}