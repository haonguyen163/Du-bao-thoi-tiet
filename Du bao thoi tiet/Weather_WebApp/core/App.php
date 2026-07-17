<?php
class App {
    protected $controller = "AuthController";
    protected $action = "login";
    protected $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        // 1. Kiểm tra Controller có tồn tại không
        if (isset($url[0]) && file_exists("../app/controllers/" . ucfirst($url[0]) . "Controller.php")) {
            $this->controller = ucfirst($url[0]) . "Controller";
            unset($url[0]);
        }

        require_once "../app/controllers/" . $this->controller . ".php";
        $this->controller = new $this->controller;

        // 2. Kiểm tra Action (Hàm) trong Controller
        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->action = $url[1];
                unset($url[1]);
            }
        }

        // 3. Lấy các tham số còn lại trên URL (nếu có)
        $this->params = $url ? array_values($url) : [];

        // 4. Chạy hàm và truyền tham số
        call_user_func_array([$this->controller, $this->action], $this->params);
    }

    private function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(trim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}