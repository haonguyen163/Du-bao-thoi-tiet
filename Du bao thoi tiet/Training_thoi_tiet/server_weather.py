from flask import Flask, request, jsonify
from flask_cors import CORS
import joblib
import pandas as pd
import numpy as np
import gc
import os
import glob
import subprocess
import sys
from datetime import datetime, timedelta
import google.generativeai as genai  # THÊM THƯ VIỆN GEMINI

app = Flask(__name__)
CORS(app)

print("=" * 60)
print("SERVER AI: USER + ADMIN + GEMINI CHAT")
print("=" * 60)

# =================================================================
# CẤU HÌNH GEMINI API (QUAN TRỌNG)
# =================================================================

GEMINI_API_KEY = "AIzaSyCoDefRhhEdCk8Dg7oEmbNaWe3xCiJeXeg"

try:
    genai.configure(api_key=GEMINI_API_KEY)
    # Sử dụng model Flash 
    gemini_model = genai.GenerativeModel('gemini-2.5-flash')
    print("-> ✅ Đã kết nối Google Gemini API")
except Exception as e:
    print(f"-> ⚠️ Lỗi cấu hình Gemini: {e}")
    gemini_model = None

# --- KHỞI TẠO BIẾN MODEL ---
model_hourly = None
model_weekly = None
model_monthly = None
model_advisor = None
features_input = []
targets_hourly = []
targets_weekly = []
targets_monthly = []
scaler_advisor = None


# --- HÀM LOAD MODEL ---
def load_models():
    global model_hourly, model_weekly, model_monthly, model_advisor, scaler_advisor
    global features_input, targets_hourly, targets_weekly, targets_monthly

    try:
        print("-> [System] Đang tải Models...")
        try:
            model_hourly = joblib.load('model_hourly.pkl', mmap_mode='r')
            targets_hourly = joblib.load('meta_targets_hourly.pkl')
            model_weekly = joblib.load('model_weekly.pkl', mmap_mode='r')
            targets_weekly = joblib.load('meta_targets_weekly.pkl')
            features_input = joblib.load('meta_features.pkl')
            model_advisor = joblib.load('model_advisor.pkl')
            scaler_advisor = joblib.load('scaler_advisor.pkl')
        except FileNotFoundError:
            print("⚠️ Chưa thấy file model cơ bản. Hãy chạy train.py trước!")
            return False

        try:
            model_monthly = joblib.load('model_monthly.pkl', mmap_mode='r')
            targets_monthly = joblib.load('meta_targets_month.pkl')
        except:
            model_monthly = None

        gc.collect()
        print("-> ✅ Server sẵn sàng!")
        return True
    except Exception as e:
        print(f"❌ Lỗi load model: {e}")
        return False


# Load lần đầu
load_models()



# API USER (Dự báo & Chat)

@app.route('/predict_all', methods=['POST'])
def predict_all():
    try:
        data = request.json
        if not model_hourly: return jsonify({'success': False, 'error': 'Server chưa có Model!'})

        input_vals = [float(data.get(f, 0)) for f in features_input]
        input_vals.append(int(data.get('hour', datetime.now().hour)))
        input_vals.append(int(data.get('month', datetime.now().month)))
        X_input = pd.DataFrame([input_vals], columns=features_input + ['hour', 'month'])

        # 1. Hourly
        pred_h = model_hourly.predict(X_input)[0]
        hourly_res = []
        temp_h = {}
        for i, col in enumerate(targets_hourly):
            parts = col.split('_hour_')
            idx = int(parts[1])
            if idx not in temp_h: temp_h[idx] = {}
            temp_h[idx][parts[0]] = round(pred_h[i], 1)
        for h in sorted(temp_h.keys()):
            hourly_res.append(
                {"time": (datetime.now() + timedelta(hours=h)).strftime("%H:00"), "temp": temp_h[h]['temperature']})

        # 2. Weekly
        pred_w = model_weekly.predict(X_input)[0]
        weekly_res = []
        temp_w = {}
        for i, col in enumerate(targets_weekly):
            parts = col.split('_day_')
            idx = int(parts[1])
            if idx not in temp_w: temp_w[idx] = {}
            temp_w[idx][parts[0]] = round(pred_w[i], 1)
        for d in sorted(temp_w.keys()):
            weekly_res.append(
                {"day_index": d, "date": (datetime.now() + timedelta(days=d)).strftime("%d/%m"), "data": temp_w[d]})

        # 3. Monthly
        monthly_res = []
        if model_monthly:
            pred_m = model_monthly.predict(X_input)[0]
            temp_m = {}
            for i, col in enumerate(targets_monthly):
                parts = col.split('_day_')
                idx = int(parts[1])
                if idx not in temp_m: temp_m[idx] = {}
                temp_m[idx][parts[0]] = round(pred_m[i], 1)
            for d in sorted(temp_m.keys()):
                monthly_res.append(
                    {"date": (datetime.now() + timedelta(days=d)).strftime("%d/%m"), "temp": temp_m[d]['temperature']})

        # 4. Advisor (Logic AI cổ điển để lấy icon/màu sắc cho UI)
        X_scaled = scaler_advisor.transform(X_input)
        code = int(model_advisor.predict(X_scaled)[0])
        advice = "Dự báo thời tiết xấu ☔" if code == 1 else "Thời tiết đẹp 🌤️"

        del X_input, pred_h, pred_w
        if model_monthly: del pred_m
        gc.collect()

        return jsonify({'success': True, 'advice': advice, 'advisor_code': code, 'hourly_forecast': hourly_res,
                        'weekly_forecast': weekly_res, 'monthly_forecast': monthly_res})
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)})


# --- API CHAT MỚI VỚI GEMINI ---
@app.route('/chat', methods=['POST'])
def chat():
    try:
        if not gemini_model:
            return jsonify({'response': "Lỗi: Chưa cấu hình API Key cho Gemini AI trong app.py!"})

        data = request.json
        user_msg = data.get('message', '')

        # Nhận biến context từ PHP (chứa temp, hum, wind, desc...)
        ctx = data.get('context', {})

        # Tạo System Prompt
        system_instruction = f"""
        Bạn là trợ lý ảo của ứng dụng thời tiết SkyCast.

        DỮ LIỆU THỜI TIẾT HIỆN TẠI ĐANG HIỂN THỊ TRÊN MÀN HÌNH KHÁCH:
        - Thành phố: {ctx.get('name', 'N/A')}
        - Nhiệt độ: {ctx.get('temp', 'N/A')}°C
        - Độ ẩm: {ctx.get('hum', 'N/A')}%
        - Gió: {ctx.get('wind', 'N/A')} m/s
        - Tình trạng: {ctx.get('desc', 'N/A')}

        NHIỆM VỤ:
        1. Trả lời câu hỏi của người dùng một cách thân thiện, ngắn gọn (dưới 100 từ).
        2. Nếu người dùng hỏi nên đi đâu/làm gì, hãy dựa vào Nhiệt độ và Tình trạng mưa để tư vấn.
        3. Sử dụng icon emoji vui vẻ.

        Người dùng hỏi: "{user_msg}"
        """

        response = gemini_model.generate_content(system_instruction)
        return jsonify({'response': response.text})

    except Exception as e:
        print("Lỗi Chat Gemini:", e)
        return jsonify({'response': "Xin lỗi, hệ thống AI đang bận. Vui lòng thử lại sau."})



# API ADMIN (QUẢN TRỊ) - GIỮ NGUYÊN


@app.route('/admin/files', methods=['GET'])
def admin_files():
    try:
        files = glob.glob("data/*.csv")
        filenames = [os.path.basename(f) for f in files]
        return jsonify({'files': filenames})
    except Exception as e:
        return jsonify({'files': [], 'error': str(e)})


@app.route('/admin/retrain', methods=['POST'])
def admin_retrain():
    try:
        print("⚠️ ADMIN REQUEST: RETRAIN...")
        TRAIN_FILE = "train_full.py"

        if not os.path.exists(TRAIN_FILE):
            return jsonify({'status': 'error', 'message': f'Không tìm thấy file {TRAIN_FILE}!'})

        process = subprocess.run([sys.executable, TRAIN_FILE], capture_output=True, text=True)

        if process.returncode == 0:
            print("-> Train xong! Reloading...")
            if load_models():
                return jsonify({'status': 'success', 'message': 'Huấn luyện thành công!',
                                'metrics': {'log_acc': 'OK', 'rf_rmse': 'OK'}})
            else:
                return jsonify({'status': 'error', 'message': 'Train xong nhưng load lỗi!'})
        else:
            print("Lỗi Train:\n" + process.stderr)
            return jsonify({'status': 'error', 'message': 'Lỗi Script: ' + process.stderr})

    except Exception as e:
        return jsonify({'status': 'error', 'message': str(e)})


# API Test nhanh cho Admin (Giữ nguyên để test logic máy học)
@app.route('/advice', methods=['POST'])
def admin_advice():
    try:
        d = request.json
        inp = pd.DataFrame([[d['temperature'], d['humidity'], d['wind_speed'], d['precipitation'], datetime.now().hour,
                             datetime.now().month]], columns=features_input + ['hour', 'month'])
        code = int(model_advisor.predict(scaler_advisor.transform(inp))[0])
        return jsonify({'message': "XẤU (Ở nhà)" if code == 1 else "TỐT (Đi chơi)", 'status_code': code})
    except:
        return jsonify({'message': "Lỗi", 'status_code': 0})


if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True, threaded=True)