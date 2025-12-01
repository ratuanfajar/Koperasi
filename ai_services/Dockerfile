FROM python:3.11-slim

# 1. Install OS dependencies
RUN apt-get update && apt-get install -y \
    tesseract-ocr \
    libtesseract-dev \
    libgl1 \
    libglib2.0-0 \
    ffmpeg \
    && apt-get clean

# 2. Set workdir
WORKDIR /app

# 3. Copy requirement first to leverage docker cache
COPY requirements.txt .

# 4. Install python dependencies
RUN pip install --no-cache-dir -r requirements.txt

# 5. Copy project files
COPY . .

# 6. Expose port (Railway auto-detects)
EXPOSE 8000

# 7. Command to run API Flask
CMD ["python", "app.py"]