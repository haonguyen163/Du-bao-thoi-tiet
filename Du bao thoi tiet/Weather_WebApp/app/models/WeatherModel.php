<?php
class WeatherModel {
    private $csvPath = 'C:\Users\hao09\Downloads\weather\weather-vn-1.csv'; 
    
    // Khai báo 2 endpoint dự phòng để tự động chuyển đổi nếu một trong hai đầu bị nghẽn
    private $apiUrlPrimary = 'http://127.0.0.1:5000/predict_all';
    private $apiUrlSecondary = 'http://localhost:5000/predict_all';

    // 1. Đọc dòng cuối cùng của file CSV
    public function getLatestWeatherInput() {
        $current_weather = [
            "city" => 1.0, "province" => 1.0, "temperature" => 30.0, 
            "humidity" => 70.0, "wind_speed" => 5.0, "precipitation" => 0.0, 
            "hour" => intval(date("H")), "month" => intval(date("m"))
        ];

        if (file_exists($this->csvPath)) {
            $lines = file($this->csvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (!empty($lines)) {
                $last_line = end($lines);
                $data_csv = str_getcsv($last_line, ",");
                $timestamp = strtotime($data_csv[0]); 

                $current_weather = [
                    "city"          => floatval($data_csv[2]),
                    "province"      => floatval($data_csv[1]),
                    "temperature"   => floatval($data_csv[3]), 
                    "humidity"      => floatval($data_csv[6]), 
                    "wind_speed"    => floatval($data_csv[11]),
                    "precipitation" => floatval($data_csv[9]), 
                    "hour"          => intval(date("H", $timestamp)), 
                    "month"         => intval(date("m", $timestamp)),
                    "year"          => date("Y", $timestamp)
                ];
            }
        }
        return $current_weather;
    }

    // 2. cURL gửi JSON sang Python AI Server với cơ chế Fallback và Headers chuẩn
    public function getAiPredictions($payload) {
        // Thử kết nối bằng Endpoint chính trước (127.0.0.1)
        $result = $this->executeCurl($this->apiUrlPrimary, $payload);
        
        // Nếu endpoint chính thất bại, tự động chuyển sang endpoint phụ (localhost) để lấy dữ liệu
        if (isset($result['success']) && $result['success'] === false) {
            $result = $this->executeCurl($this->apiUrlSecondary, $payload);
        }
        
        return $result;
    }

    // Hàm thực thi cURL lõi được tối ưu hóa mạng nội bộ XAMPP
    private function executeCurl($url, $payload) {
        try {
            $ch = curl_init($url);
            
            // Cấu hình payload dữ liệu JSON[cite: 11]
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