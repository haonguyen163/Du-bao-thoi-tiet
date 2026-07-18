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
import google.generativeai as genai

app = Flask(__name__)
CORS(app)

print("=" * 60)
print("SERVER AI: STANDARD MASTER VERSION")
print("=" * 60)

# CẤU HÌNH GEMINI API KEY MỚI CỦA CẬU
GEMINI_API_KEY = " "#do chính sach bao mat nen ko the de API key gemini (api_python)

try:
    genai.configure(api_key=GEMINI_API_KEY)
    gemini_model = genai.GenerativeModel("gemini-3.1-flash-lite")
    print("-> ✅ Đã kết nối Google Gemini API")
except Exception as e:
    print(f"-> ⚠️ Lỗi cấu hình Gemini: {e}")
    gemini_model = None

# --- KHỞI TẠO BIẾN MODEL MÁY HỌC ---
model_hourly = None
model_weekly = None
model_monthly = None
model_advisor = None
features_input = []
targets_hourly = []
targets_weekly = []
targets_monthly = []
scaler_advisor = None

def load_models():
    global model_hourly, model_weekly, model_monthly, model_advisor, scaler_advisor
    global features_input, targets_hourly, targets_weekly, targets_monthly

    try:
        print("-> [System] Đang tải Models...")
        model_hourly = joblib.load('model_hourly.pkl', mmap_mode='r')
        targets_hourly = joblib.load('meta_targets_hourly.pkl')
        model_weekly = joblib.load('model_weekly.pkl', mmap_mode='r')
        targets_weekly = joblib.load('meta_targets_weekly.pkl')
        features_input = joblib.load('meta_features.pkl')
        model_advisor = joblib.load('model_advisor.pkl')
        scaler_advisor = joblib.load('scaler_advisor.pkl')
        try:
            model_monthly = joblib.load('model_monthly.pkl', mmap_mode='r')
            targets_monthly = joblib.load('meta_targets_month.pkl')
        except:
            model_monthly = None

        gc.collect()
        print("-> ✅ Server sẵn sàng phục vụ!")
        return True
    except Exception as e:
        print(f"❌ Lỗi load model: {e}")
        return False

load_models()

@app.route('/predict_all', methods=['POST'])
def predict_all():
    try:
        data = request.json
        if not model_hourly:
            return jsonify({'success': False, 'error': 'Server chưa có Model!'})

        input_vals = [float(data.get(f, 0)) for f in features_input]
        input_vals.append(int(data.get('hour', datetime.now().hour)))
        input_vals.append(int(data.get('month', datetime.now().month)))
        X_input = pd.DataFrame([input_vals], columns=features_input + ['hour', 'month'])

        # 1. Dự báo Hourly
        pred_h = model_hourly.predict(X_input)[0]
        hourly_res = []
        temp_h = {}
        for i, col in enumerate(targets_hourly):
            parts = col.split('_hour_')
            idx = int(parts[1])
            if idx not in temp_h: temp_h[idx] = {}
            temp_h[idx][parts[0]] = round(pred_h[i], 1)
        for h in sorted(temp_h.keys()):
            hourly_res.append({
                "time": (datetime.now() + timedelta(hours=h)).strftime("%H:00"),
                "temp": temp_h[h]['temperature']
            })

        # 2. Dự báo Weekly
        pred_w = model_weekly.predict(X_input)[0]
        weekly_res = []
        temp_w = {}
        for i, col in enumerate(targets_weekly):
            parts = col.split('_day_')
            idx = int(parts[1])
            if idx not in temp_w: temp_w[idx] = {}
            temp_w[idx][parts[0]] = round(pred_w[i], 1)
        for d in sorted(temp_w.keys()):
            weekly_res.append({
                "day_index": d,
                "date": (datetime.now() + timedelta(days=d)).strftime("%d/%m"),
                "data": temp_w[d]
            })

        # 3. Dự báo Monthly
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
                monthly_res.append({
                    "date": (datetime.now() + timedelta(days=d)).strftime("%d/%m"),
                    "temp": temp_m[d]['temperature']
                })

        # 4. Phân loại chỉ số Advisor quyết định giao diện UI
        X_scaled = scaler_advisor.transform(X_input)
        code = int(model_advisor.predict(X_scaled)[0])
        advice = "Dự báo thời tiết xấu ☔" if code == 1 else "Thời tiết đẹp 🌤️"

        del X_input, pred_h, pred_w
        if model_monthly: del pred_m
        gc.collect()

        return jsonify({
            'success': True,
            'advice': advice,
            'advisor_code': code,
            'hourly': hourly_res,
            'weekly': weekly_res,
            'monthly': monthly_res
        })
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)})

@app.route('/chat', methods=['POST'])
def chat():
    try:
        if not gemini_model:
            return jsonify({'response': "Lỗi: Chưa cấu hình API Key cho Gemini AI!"})

        data = request.json
        user_msg = data.get('message', '')
        ctx = data.get('context', {})

        system_instruction = f"""
        Bạn là trợ lý ảo thời tiết SkyCast.
        DỮ LIỆU THỜI TIẾT THỰC TẾ: Nhiệt độ: {ctx.get('temp', 'N/A')}°C, Độ ẩm: {ctx.get('hum', 'N/A')}%, Gió: {ctx.get('wind', 'N/A')} m/s. Trạng thái: {ctx.get('desc', 'N/A')}.
        NHIỆM VỤ: Trả lời câu hỏi thật ngắn gọn dưới 70 từ, thân thiện và dùng emoji vui vẻ. Tư vấn trang phục/phối đồ phù hợp dựa trên thông số trên.
        Người dùng hỏi: "{user_msg}"
        """
        response = gemini_model.generate_content(system_instruction)
        return jsonify({'response': response.text})
    except Exception as e:
        print("Lỗi Chat Gemini:", e)
        return jsonify({'response': "Xin lỗi, hệ thống AI đang bận xử lý phản hồi. Cậu vui lòng thử lại sau."})

# --- API ADMIN GIỮ NGUYÊN ---
@app.route('/admin/files', methods=['GET'])
def admin_files():
    try:
        files = glob.glob("data/*.csv")
        return jsonify({'files': [os.path.basename(f) for f in files]})
    except Exception as e:
        return jsonify({'files': [], 'error': str(e)})

@app.route('/admin/retrain', methods=['POST'])
def admin_retrain():
    try:
        TRAIN_FILE = "AI_Training.py"
        if not os.path.exists(TRAIN_FILE): return jsonify({'status': 'error', 'message': 'Không tìm thấy file!'})
        process = subprocess.run([sys.executable, TRAIN_FILE], capture_output=True, text=True)
        if process.returncode == 0 and load_models():
            return jsonify({'status': 'success', 'message': 'Huấn luyện thành công!'})
        return jsonify({'status': 'error', 'message': 'Lỗi Script: ' + process.stderr})
    except Exception as e:
        return jsonify({'status': 'error', 'message': str(e)})

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True, threaded=True)