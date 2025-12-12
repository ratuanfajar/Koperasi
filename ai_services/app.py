from flask import Flask, request, jsonify
import os
from werkzeug.utils import secure_filename
from config.settings import OUTPUT_DIR, STATIC_DIR
from utils.image_utils import final_pipeline
from utils.ocr_utils import run_ocr, convert_paddleocr_to_json, init_ocr
from utils.llm_utils import analyze_ocr_transaction
from flask_cors import CORS


ALLOWED_EXT = {"png", "jpg", "jpeg"}

app = Flask(__name__)
CORS(app, resources={r"/*": {"origins": "*"}})

app.config['UPLOAD_FOLDER'] = STATIC_DIR

init_ocr() 


def allowed_file(filename):
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in ALLOWED_EXT

def log_info(msg):
    app.logger.info(msg)
    print(f"[INFO] {msg}")

def log_error(msg):
    app.logger.error(msg)
    print(f"[ERROR] {msg}")

@app.route('/')
def home():
    return "Hello World!"

@app.route('/health')
def health():
    return jsonify({'status': 'ok'})


@app.route('/analyze', methods=['POST'])
def analyze_receipt():
    if 'file' not in request.files:
        return jsonify({'error': 'file field is required'}), 400

    file = request.files['file']
    if file.filename == '':
        return jsonify({'error': 'filename empty'}), 400

    if not allowed_file(file.filename):
        return jsonify({'error': 'file type not allowed'}), 400

    fname = secure_filename(file.filename)
    save_path = os.path.join(app.config['UPLOAD_FOLDER'], fname)
    os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)
    file.save(save_path)
    log_info(f"Uploaded file saved: {save_path}")

    try:
        # 1) preprocessing pipeline
        processed_path = final_pipeline(save_path, out_dir=OUTPUT_DIR, filename=f'pre_{fname}')
        log_info(f"Preprocessing success: {processed_path}")

        # 2) OCR
        ocr_raw = run_ocr(processed_path)
        ocr_json = convert_paddleocr_to_json(ocr_raw[0])
        log_info("OCR succeeded")

        # 3) LLM analysis
        llm_result = analyze_ocr_transaction(ocr_json)
        log_info("LLM analysis completed")

        return jsonify({
            'ocr': ocr_json,
            'llm': llm_result
        })

    except Exception as e:
        log_error(f"Processing error: {str(e)}")
        # Print stack trace di console untuk debug
        import traceback
        traceback.print_exc()
        return jsonify({"error": str(e)}), 500


if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)
