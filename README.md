## 📊 Đánh giá và So sánh Mô hình (Model Evaluation)

Để đảm bảo độ chính xác cho hệ thống dự báo thời tiết, dự án đã tiến hành huấn luyện và đối chiếu hiệu năng của 6 thuật toán Học máy và Thống kê khác nhau. Dưới đây là bảng dashboard tổng hợp kết quả:

![So sánh 6 mô hình AI](https://github.com/haonguyen163/Du-bao-thoi-tiet/blob/main/img_training_model.jpg)

**Phân tích kết quả thực nghiệm:**
* **Mô hình học có giám sát:** Thuật toán **Random Forest** cho thấy sự ổn định trong bài toán hồi quy với sai số RMSE ở mức 1.53, bám khá sát đường chuẩn. Trong khi đó, **Logistic Regression** hoàn thành xuất sắc nhiệm vụ phân loại trạng thái thời tiết với độ chính xác lên tới 99.4%.
* **Phân tích xu hướng thời gian (Time Series):** Các mô hình như **ARIMA**, **Prophet** (của Facebook) và **Holt-Winters** được triển khai để nắm bắt tính mùa vụ và xu hướng nhiệt độ/độ ẩm dài hạn, cho ra các đường dự báo khá sát với dữ liệu thực tế.
* **Học không giám sát:** Triển khai **K-Means** để tự động phân nhóm các đới khí hậu dựa trên đặc trưng dữ liệu ẩn.

**👉 Kết luận:** Hệ thống sử dụng luồng dự báo kép: Ứng dụng Random Forest để xử lý bài toán Hồi quy (dự báo nhiệt độ đa khung giờ: theo giờ, tuần, tháng) và Logistic Regression cho bài toán Phân loại (đánh giá trạng thái thời tiết tổng quan). Đồng thời tích hợp LLM (Google Gemini) làm trợ lý ảo tương tác trực tiếp.
