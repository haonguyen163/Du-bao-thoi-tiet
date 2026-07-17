<?php
class WeatherModel {
    private $csvPath = 'C:\Users\hao09\Downloads\weather\weather-vn-4.csv'; 
    // 🛠️ Kiểm tra xem route ở server_weather.py của cậu có đúng là /predict_all không nhé
   private $apiUrl = 'http://localhost:5000/predict_all'; 

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

    // 2. cURL gửi JSON sang Python AI Server
    public function getAiPredictions($payload) {
        try {
            $ch = curl_init($this->apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            // 🛠️ ĐÃ SỬA: Tăng timeout lên 15 giây để kịp đợi Google Gemini API phản hồi advice
            curl_setopt($ch, CURLOPT_TIMEOUT, 15); 

            $response = curl_exec($ch);
            
            // Check lỗi cURL nếu có
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
                curl_close($ch);
                return ['success' => false, 'error' => 'cURL Error: ' . $error_msg];
            }
            
            curl_close($ch);

            if ($response) {
                return json_decode($response, true);
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
        return ['success' => false, 'error' => 'Không thể kết nối Server Python AI!'];
    }
}