# 🌤️ Vietnam Weather Data Pipeline, BI Dashboard & AI Forecasting

## 📌 Tổng quan dự án (Project Overview)
Dự án End-to-End Data Pipeline kết hợp phân tích trực quan, theo dõi và dự báo xu hướng thời tiết tại các tỉnh thành Việt Nam. Hệ thống tự động thu thập dữ liệu, làm sạch, huấn luyện các mô hình Machine Learning và hiển thị báo cáo thông qua Dashboard Power BI với thiết kế bento-grid hiện đại. Đặc biệt, dự án tích hợp trợ lý ảo AI nhằm tối ưu hóa trải nghiệm truy vấn dữ liệu của người dùng.

## 🛠️ Công nghệ sử dụng (Tech Stack)
* **Data Source:** OpenWeatherMap API.
* **Data Engineering:** Python (Pandas) để Data Cleaning & Transformation.
* **Machine Learning & AI:** 
  * *Mô hình triển khai chính:* Random Forest, Logistic Regression.
  * *Mô hình đối chiếu & phân tích:* ARIMA, Prophet, Holt-Winters, K-Means.
  * *LLM Integration:* Google Gemini (Trợ lý ảo thời tiết).
* **Data Visualization:** Power BI.
* **Database & Infrastructure:** MySQL, SQL Server, Docker.

## 📊 Điểm nổi bật của Dashboard (Dashboard Highlights)
Dashboard được thiết kế theo tư duy Bento-Grid, tối ưu hóa UI/UX giúp nhanh chóng nắm bắt các chỉ số quan trọng:
* **Bản đồ nhiệt (Choropleth Map):** Trực quan hóa phân bổ mức nhiệt độ/lượng mưa trên khắp 63 tỉnh thành Việt Nam.
* **Phân tích Tương quan (Scatter Plot):** Khám phá mối liên hệ mật thiết giữa Nhiệt độ và Độ ẩm.
* **Theo dõi Dòng thời gian (Time-series Analysis):** So sánh chênh lệch giữa *Nhiệt độ Thực tế* và *Nhiệt độ Cảm nhận* theo từng giờ.
* **Phát hiện Bất thường (Anomaly Detection):** Dễ dàng nhận diện các đỉnh điểm cực đoan của thời tiết (VD: Gió giật mạnh, lượng mưa đột biến do bão/áp thấp).

## 📸 Giao diện Báo cáo (Dashboard Preview)
![Vietnam Weather Power BI Dashboard](https://github.com/haonguyen163/Du-bao-thoi-tiet/blob/main/ShowCase/img_powerbi.jpg)

File Power BI: https://github.com/haonguyen163/Du-bao-thoi-tiet/blob/main/Du%20bao%20thoi%20tiet
## 💡 Insight Rút ra (Key Insights)
1. Nhiệt độ cảm nhận (Feels like) luôn có xu hướng chênh lệch cao hơn so với nhiệt độ thực tế do ảnh hưởng của độ ẩm đặc trưng tại Việt Nam.
2. Trạng thái thời tiết thống trị trong tập dữ liệu là "Overcast clouds" (Trời nhiều mây), ảnh hưởng trực tiếp đến biến động lượng mưa trong các tháng đánh giá.
3. Tốc độ gió và Lượng mưa ghi nhận các gai dữ liệu (spikes) rõ rệt tại một số thời điểm, trùng khớp với các đợt biến động thời tiết mạnh.

## 🤖 Đánh giá và So sánh Mô hình (Model Evaluation)
Để đảm bảo độ tin cậy cho hệ thống dự báo, dự án đã tiến hành huấn luyện và đối chiếu hiệu năng chéo giữa 6 thuật toán Học máy và Thống kê.

![So sánh 6 mô hình AI](https://github.com/haonguyen163/Du-bao-thoi-tiet/blob/main/ShowCase/img_training_model.jpg)

**Phân tích kết quả thực nghiệm:**
* **Học có giám sát (Supervised Learning):** Thuật toán **Random Forest** cho thấy sự ổn định vượt trội trong bài toán hồi quy với sai số RMSE ở mức 1.53, bám sát đường chuẩn. Trong khi đó, **Logistic Regression** hoàn thành xuất sắc nhiệm vụ phân loại trạng thái thời tiết với độ chính xác (Accuracy) đạt 99.4%.
* **Phân tích chuỗi thời gian (Time Series):** Các mô hình **ARIMA**, **Prophet** và **Holt-Winters** được khai thác để nắm bắt tính mùa vụ và xu hướng nhiệt độ/độ ẩm dài hạn, cho ra các đường dự báo bám sát với dữ liệu thực tế.
* **Học không giám sát (Unsupervised Learning):** Triển khai **K-Means** để tự động phân cụm (clustering) các đới khí hậu dựa trên hệ đặc trưng dữ liệu ẩn.

**👉 Kiến trúc AI cốt lõi:** Hệ thống chốt hạ sử dụng luồng dự báo đa tầng:
1. Ứng dụng **Random Forest** để xử lý bài toán Hồi quy (dự báo nhiệt độ đa khung giờ: theo giờ, tuần, tháng).
2. Ứng dụng **Logistic Regression** cho bài toán Phân loại (đánh giá trạng thái thời tiết tổng quan).
3. Tích hợp thành công **LLM (Google Gemini)** làm trợ lý ảo, cho phép người dùng truy vấn thông tin linh hoạt bằng ngôn ngữ tự nhiên.

## 🚀 Hướng phát triển (Future Scope)
* Tự động hóa hoàn toàn luồng ETL, chuyển dịch từ Batch Processing sang Real-time Data Streaming.
* Container hóa toàn bộ hệ thống và triển khai (Deploy) lên môi trường Cloud (AWS/GCP) để tối ưu hiệu suất và khả năng mở rộng.
