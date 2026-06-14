import pandas as pd
import numpy as np
import glob
import warnings
import matplotlib.pyplot as plt
import seaborn as sns

# --- THƯ VIỆN MACHINE LEARNING (TEAM A)
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestRegressor
from sklearn.linear_model import LogisticRegression
from sklearn.cluster import KMeans
from sklearn.preprocessing import StandardScaler
from sklearn.metrics import mean_squared_error, accuracy_score, confusion_matrix

# --- THƯ VIỆN TIME SERIES (TEAM B)
# Thử import statsmodels
try:
    from statsmodels.tsa.arima.model import ARIMA
    from statsmodels.tsa.holtwinters import ExponentialSmoothing

    has_statsmodels = True
except ImportError:
    has_statsmodels = False
    print("⚠️ Chưa cài statsmodels (Sẽ bỏ qua ARIMA & HW)")

# Xử lý thư viện Prophet
try:
    from prophet import Prophet

    has_prophet = True
except ImportError:
    has_prophet = False
    print("⚠️ Chưa cài Prophet (Bỏ qua mô hình này)")

warnings.filterwarnings('ignore')


def main():
    print("=" * 60)
    print("DEMO BÁO CÁO: CUỘC CHIẾN 6 MÔ HÌNH (3 ML vs 3 TimeSeries)")
    print("=" * 60)

    # --- 1. ĐỌC DỮ LIỆU ---
    print("\n[1] ĐANG TẢI DỮ LIỆU...")
    list_files = sorted(glob.glob("data/weather-vn-*.csv"))
    if not list_files:
        print("Lỗi: Không thấy file data trong thư mục 'data/'!")
        return

    df = pd.concat((pd.read_csv(f) for f in list_files), ignore_index=True)
    df['time'] = pd.to_datetime(df['time'])
    df = df.sort_values(by='time')

    # Feature Engineering
    df['hour'] = df['time'].dt.hour
    df['month'] = df['time'].dt.month
    features = ['temperature', 'humidity', 'wind_speed', 'precipitation']
    for col in features:
        if col not in df.columns: df[col] = 0
    df[features] = df[features].fillna(method='ffill')

    print(f"-> Tổng dữ liệu: {len(df)} dòng.")

    # Tạo lưới biểu đồ (2 hàng, 3 cột = 6 ô)
    fig = plt.figure(figsize=(18, 10))
    fig.suptitle('SO SÁNH 6 MÔ HÌNH AI DỰ BÁO THỜI TIẾT', fontsize=16, color='#0D8ABC', fontweight='bold')
    # TEAM A: MACHINE LEARNING


    # --- MODEL 1: RANDOM FOREST ---
    print("\n[Mô hình 1] RANDOM FOREST...")
    X = df[['humidity', 'wind_speed', 'precipitation', 'hour', 'month']]
    y = df['temperature']
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

    rf = RandomForestRegressor(n_estimators=50, max_depth=10, random_state=42)
    rf.fit(X_train, y_train)
    y_pred_rf = rf.predict(X_test)
    rmse_rf = np.sqrt(mean_squared_error(y_test, y_pred_rf))
    print(f"-> RMSE: {rmse_rf:.2f}")

    ax1 = fig.add_subplot(2, 3, 1)
    # Vẽ mẫu 50 điểm đầu tiên của tập test
    subset_len = min(50, len(y_test))
    ax1.scatter(y_test.values[:subset_len], y_pred_rf[:subset_len], color='blue', alpha=0.6, label='Dự báo')
    ax1.plot([y_test.min(), y_test.max()], [y_test.min(), y_test.max()], 'r--', label='Chuẩn')
    ax1.set_title(f'1. Random Forest (RMSE: {rmse_rf:.2f})')
    ax1.legend()

    # --- MODEL 2: LOGISTIC REGRESSION ---
    print("\n[Mô hình 2] LOGISTIC REGRESSION (Advisor)...")
    df['bad_weather'] = ((df['precipitation'] > 0.5) | (df['temperature'] > 37)).astype(int)
    X_log = df[['temperature', 'humidity', 'wind_speed', 'precipitation']]
    y_log = df['bad_weather']
    X_train_l, X_test_l, y_train_l, y_test_l = train_test_split(X_log, y_log, test_size=0.2)

    scaler = StandardScaler()
    X_train_s = scaler.fit_transform(X_train_l)
    X_test_s = scaler.transform(X_test_l)

    log_model = LogisticRegression()
    log_model.fit(X_train_s, y_train_l)
    acc_log = accuracy_score(y_test_l, log_model.predict(X_test_s))
    print(f"-> Accuracy: {acc_log * 100:.2f}%")

    ax2 = fig.add_subplot(2, 3, 2)
    cm = confusion_matrix(y_test_l, log_model.predict(X_test_s))
    sns.heatmap(cm, annot=True, fmt='d', cmap='Blues', ax=ax2)
    ax2.set_title(f'2. Logistic Reg (Acc: {acc_log * 100:.1f}%)')
    ax2.set_xlabel('0: Tốt | 1: Xấu')

    # --- MODEL 3: K-MEANS ---
    print("\n[Mô hình 3] K-MEANS CLUSTERING...")
    kmeans = KMeans(n_clusters=3, random_state=42)
    # Lấy mẫu 1000 điểm để chạy cho nhanh nếu data quá lớn
    sample_df = df[['temperature', 'humidity']].sample(min(2000, len(df)))
    clusters = kmeans.fit_predict(sample_df)

    ax3 = fig.add_subplot(2, 3, 3)
    ax3.scatter(sample_df['temperature'], sample_df['humidity'], c=clusters, cmap='viridis', s=10)
    ax3.set_title('3. K-Means (Phân nhóm khí hậu)')
    ax3.set_xlabel('Nhiệt độ')
    ax3.set_ylabel('Độ ẩm')


    # TEAM B: TIME SERIES (Dự báo theo chuỗi thời gian)


    # Chuẩn bị dữ liệu: Tính trung bình ngày
    daily_temp = df.groupby(df['time'].dt.date)['temperature'].mean()

    # FIX LỖI DIMENSION: Tự động tính toán số lượng data train
    total_days = len(daily_temp)
    train_size = int(total_days * 0.8)  # Lấy 80% để train
    if train_size < 10: train_size = total_days - 2  # Phòng trường hợp data quá ít

    train_data = daily_temp.values[:train_size]
    test_data = daily_temp.values[train_size:]

    # --- MODEL 4: ARIMA ---
    print("\n[Mô hình 4] ARIMA...")
    ax4 = fig.add_subplot(2, 3, 4)
    if has_statsmodels and len(train_data) > 5:
        try:
            # Dùng order đơn giản (1,1,0) để dễ hội tụ hơn
            model_arima = ARIMA(train_data, order=(1, 1, 0)).fit()
            forecast_arima = model_arima.forecast(steps=len(test_data))

            # Vẽ biểu đồ sử dụng range linh hoạt (len)
            ax4.plot(range(len(train_data)), train_data, label='Lịch sử')
            ax4.plot(range(len(train_data), len(train_data) + len(test_data)), forecast_arima, label='Dự báo',
                     color='red')
            ax4.set_title('4. ARIMA (Thống kê)')
            ax4.legend()
            print("-> ARIMA chạy thành công.")
        except Exception as e:
            print(f"Lỗi ARIMA: {e}")
            ax4.text(0.5, 0.5, 'Lỗi hội tụ ARIMA', ha='center')
    else:
        ax4.text(0.5, 0.5, 'Thiếu thư viện/Data ít', ha='center')

    # --- MODEL 5: PROPHET ---
    print("\n[Mô hình 5] FACEBOOK PROPHET...")
    ax5 = fig.add_subplot(2, 3, 5)
    if has_prophet:
        df_prophet = daily_temp.reset_index()
        df_prophet.columns = ['ds', 'y']

        m = Prophet()
        m.fit(df_prophet)
        future = m.make_future_dataframe(periods=30)
        forecast = m.predict(future)

        # Vẽ 100 ngày cuối (hoặc toàn bộ nếu ít hơn 100)
        plot_len = min(100, len(df_prophet))
        ax5.plot(df_prophet['ds'].tail(plot_len), df_prophet['y'].tail(plot_len), label='Thực tế')
        ax5.plot(forecast['ds'].tail(plot_len + 30), forecast['yhat'].tail(plot_len + 30), label='Prophet',
                 color='orange')
        ax5.set_title('5. Prophet (Facebook)')
        ax5.legend()
    else:
        ax5.text(0.5, 0.5, 'Chưa cài Prophet', ha='center')

    # --- MODEL 6: HOLT-WINTERS ---
    print("\n[Mô hình 6] HOLT-WINTERS...")
    ax6 = fig.add_subplot(2, 3, 6)
    if has_statsmodels and len(train_data) > 5:
        try:
            # Holt-Winters (Exponential Smoothing)
            model_hw = ExponentialSmoothing(train_data, trend='add', seasonal=None).fit()
            pred_hw = model_hw.forecast(steps=len(test_data))

            # FIX LỖI SHAPE: Dùng range(len(...)) thay vì số cứng
            ax6.plot(range(len(train_data)), train_data, label='Train')

            if len(test_data) > 0:
                ax6.plot(range(len(train_data), len(train_data) + len(test_data)), test_data, 'g--', label='Thực tế')

            ax6.plot(range(len(train_data), len(train_data) + len(test_data)), pred_hw, color='purple', linewidth=2,
                     label='Holt-Winters')

            ax6.set_title('6. Holt-Winters (Làm mượt)')
            ax6.legend()
            print("-> Holt-Winters dự báo thành công.")
        except Exception as e:
            print(f"Lỗi HW: {e}")
            ax6.text(0.5, 0.5, f'Lỗi HW', ha='center')
    else:
        ax6.text(0.5, 0.5, 'Data quá ít', ha='center')

    # --- HIỂN THỊ ---
    print("\n" + "=" * 60)
    print("HOÀN TẤT! ĐANG MỞ BIỂU ĐỒ...")
    plt.tight_layout()
    plt.show()


if __name__ == "__main__":
    main()