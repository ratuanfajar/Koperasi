from paddleocr import PaddleOCR

OCR = None

def init_ocr():
    global OCR
    if OCR is None:
        OCR = PaddleOCR(lang='id')


def run_ocr(image_path):
    global OCR
    if OCR is None:
        init_ocr()
    result = OCR.predict(image_path)
    return result


def convert_paddleocr_to_json(result):
    rec_texts = result.get('rec_texts', [])
    rec_scores = result.get('rec_scores', [])
    rec_polys = result.get('rec_polys', [])

    n = min(len(rec_texts), len(rec_scores), len(rec_polys))

    structured_data = []
    for i in range(n):
        text = rec_texts[i]
        score = rec_scores[i]
        poly = rec_polys[i]

        xs = [p[0] for p in poly]
        ys = [p[1] for p in poly]
        avg_x, avg_y = sum(xs) / len(xs), sum(ys) / len(ys)

        structured_data.append({
            'id': i + 1,
            'text': text.strip(),
            'confidence': round(float(score), 4),
            'position': {'x': round(avg_x, 2), 'y': round(avg_y, 2)}
        })

    json_data = {
        'input_path': result.get('input_path', ''),
        'page_index': result.get('page_index', 0),
        'num_items': n,
        'texts': structured_data
    }

    return json_data