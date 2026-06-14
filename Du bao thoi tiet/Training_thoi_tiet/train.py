import pandas as pd
import numpy as np
import glob
import joblib
import warnings
import gc

from sklearn.ensemble import RandomForestRegressor
from sklearn.multioutput import MultiOutputRegressor
from sklearn.linear_model import LogisticRegression
from sklearn.preprocessing import StandardScaler

warnings.filterwarnings('ignore')


def optimize_ram(df):

    for col in df.columns:
        if df[col].dtype == 'float64':
            df[col] = df[col].astype('float32')
        if df[col].dtype == 'int64':
            df[col] = df[col].astype('int32')
    return df


def main():
    print("=" * 60)
    print("HUẤN LUYỆN AI: ")
    print("=" * 60)

    # 1. ĐỌC DỮ LIỆU
    list_files = sorted(glob.glob("data/weather-vn-*.csv"))
    if not list_files:
        print("LỖI: Không tìm thấy file CSV!")
        return

    print(f"-> Đang đọc dữ liệu...")
    df = pd.concat((pd.read_csv(f) for f in list_files), ignore_index=True)
    df['time'] = pd.to_datetime(df['time'])
    df = df.sort_values(by='time')

    # --- CẤU HÌNH DỮ LIỆU ---
    # phần này chạy 100.000 dòng để đỡ lag khi chạy
    LIMIT_ROWS = 100000
    if len(df) > LIMIT_ROWS:
        print(f"-> Sử dụng {LIMIT_ROWS} dòng dữ liệu mới nhất...")
        df = df.tail(LIMIT_ROWS)

    # Tối ưu RAM
    df = optimize_ram(df)

    # Feature Engineering
    features = ['temperature', 'humidity', 'wind_speed', 'precipitation']
    for col in features:
        if col not in df.columns: df[col] = 0.0
    df[features] = df[features].fillna(method='ffill').fillna(0)

    df['hour'] = df['time'].dt.hour
    df['month'] = df['time'].dt.month

    # --- CẤU HÌNH FULL POWER ---
    # n_jobs=-1: Dùng TẤT CẢ nhân CPU
    # n_estimators=100: AI thông minh
    rf_config = RandomForestRegressor(n_estimators=100, max_depth=15, n_jobs=-1, random_state=42)


    # A. TRAIN HOURLY (24H)

    print("\n[A] Train Hourly (24h)...")
    FORECAST_HOURS = 24
    target_cols_h = []

    df_temp = df.copy()
    for i in range(1, FORECAST_HOURS + 1):
        for feat in features:
            col_name = f"{feat}_hour_{i}"
            df_temp[col_name] = df_temp[feat].shift(-i)
            target_cols_h.append(col_name)
    df_temp = df_temp.dropna()

    X = df_temp[features + ['hour', 'month']]
    y = df_temp[target_cols_h]

    # Train
    model_h = MultiOutputRegressor(rf_config, n_jobs=-1)
    model_h.fit(X, y)

    joblib.dump(model_h, 'model_hourly.pkl')
    joblib.dump(target_cols_h, 'meta_targets_hourly.pkl')
    print("   -> Xong Hourly.")

    del df_temp, X, y, model_h
    gc.collect()


    # B. TRAIN WEEKLY (7 NGÀY)

    print("\n[B] Train Weekly (7 ngày)...")
    FORECAST_DAYS_W = 7
    target_cols_w = []

    df_temp = df.copy()
    for i in range(1, FORECAST_DAYS_W + 1):
        for feat in features:
            col_name = f"{feat}_day_{i}"
            df_temp[col_name] = df_temp[feat].shift(-24 * i)
            target_cols_w.append(col_name)
    df_temp = df_temp.dropna()

    X = df_temp[features + ['hour', 'month']]
    y = df_temp[target_cols_w]

    model_w = MultiOutputRegressor(rf_config, n_jobs=-1)
    model_w.fit(X, y)

    joblib.dump(model_w, 'model_weekly.pkl')
    joblib.dump(target_cols_w, 'meta_targets_weekly.pkl')
    joblib.dump(features, 'meta_features.pkl')
    print("   -> Xong Weekly.")

    del df_temp, X, y, model_w
    gc.collect()


    # C. TRAIN MONTHLY (30 NGÀY)

    print("\n[C] Train Monthly (30 ngày)...")
    FORECAST_DAYS_M = 30
    target_cols_m = []

    df_temp = df.copy()
    for i in range(1, FORECAST_DAYS_M + 1):
        for feat in features:
            col_name = f"{feat}_day_{i}"
            df_temp[col_name] = df_temp[feat].shift(-24 * i)
            target_cols_m.append(col_name)
    df_temp = df_temp.dropna()

    X = df_temp[features + ['hour', 'month']]
    y = df_temp[target_cols_m]

    # Model tháng output rất lớn
    rf_month = RandomForestRegressor(n_estimators=60, max_depth=12, n_jobs=-1, random_state=42)
    model_m = MultiOutputRegressor(rf_month, n_jobs=-1)

    model_m.fit(X, y)

    joblib.dump(model_m, 'model_monthly.pkl')
    joblib.dump(target_cols_m, 'meta_targets_month.pkl')
    print("   -> Xong Monthly.")

    del df_temp, X, y, model_m
    gc.collect()
    # D. ADVISOR
    print("\n[D] Train Advisor...")

    def rule(row):
        if (row['precipitation'] > 0.5) or (row['temperature'] > 37) or (row['temperature'] < 12): return 1
        return 0

    y_adv = df.apply(rule, axis=1)
    scaler = StandardScaler()
    X_scaled = scaler.fit_transform(df[features + ['hour', 'month']])

    log_model = LogisticRegression()
    log_model.fit(X_scaled, y_adv)

    joblib.dump(log_model, 'model_advisor.pkl')
    joblib.dump(scaler, 'scaler_advisor.pkl')

    print("\nHOÀN TẤT! ")


if __name__ == "__main__":
    main()