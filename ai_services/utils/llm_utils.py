import os
from groq import Groq
from config.settings import GROQ_API_KEY

PROMPT_TEMPLATE = """
Kamu adalah ahli akuntansi yang bertugas untuk menganalisis dokumen bukti transaksi keuangan dan merekomendasikan beberapa kemungkinan kode akun
berdasarkan hasil OCR dari dokumen transaksi keuangan (seperti kwitansi, nota, invoice, atau slip pembayaran) untuk memberikan ringkasan transaksi
beserta **3 rekomendasi kode akun** dari Chart of Accounts (COA) standar.

Gunakan hasil OCR berikut sebagai konteks:
{ocr}

---

### TUGAS:
1. Analisis isi teks hasil OCR dan identifikasi elemen-elemen utama transaksi berikut:
   - `tanggal_transaksi`: tanggal transaksi dalam format YYYY-MM-DD.
   - `pihak_terlibat`: nama pihak yang menerima atau memberi uang.
   - `deskripsi_transaksi`: maksud, tujuan, atau keterangan transaksi.
   - `nominal_total`: jumlah uang yang tercantum.
   - `mata_uang`: mis. IDR, USD, dll.
   - `tipe_transaksi`: "debit" atau "kredit" (sesuaikan dengan konteks transaksi).
   - `items`: (opsional) HANYA JIKA daftar barang/jasa bila terlihat jelas di teks seperti dari nota.

2. Berdasarkan hasil analisis, berikan **3 rekomendasi akun** dari COA yang paling relevan.
   Setiap rekomendasi harus memuat:
   - `kode_akun` (contoh: "511")
   - `nama_akun` (contoh: "Beban Perlengkapan Kantor")
   - `confidence` (nilai 0–1, perkiraan tingkat keyakinan)
   - `alasan` (penjelasan singkat mengapa akun tersebut cocok)

---

### PANDUAN ANALISIS:
1. **Gunakan logika akuntansi umum:**
   - 1xx → Aset
   - 2xx → Kewajiban
   - 3xx → Modal
   - 4xx → Pendapatan
   - 5xx → Beban / Pengeluaran operasional
   - 6xx → Beban non-operasional
   - 7xx → Pendapatan non-operasional
   - Jika ada istilah seperti "Kas Kecil", "Kas Utama" → anggap akun Kas (1xx).

2. **Bersihkan teks OCR:**
   - Hapus simbol aneh, karakter rusak, dan baris tidak relevan.
   - Gabungkan kalimat yang seharusnya satu konteks.

3. **Tangani nilai numerik:**
   - Ubah format seperti “1.000,50” → 1000.50.
   - Tentukan apakah itu debit/kredit berdasar narasi (“pembelian”, “penerimaan”, dll).

4. **Tambahkan konteks transaksi:**
   - Contoh interpretasi:
     - "Pembayaran" → beban atau pengeluaran (debit)
     - "Penerimaan" → pendapatan (kredit)
     - "Setoran modal" → modal (3xxx)
     - "Pembelian alat tulis" → beban perlengkapan kantor (5xxx)

5. **Bagian rekomendasi akun (inti reasoning):**
   - Analisis teks untuk menentukan kategori transaksi.
   - Pilih 3 kode akun paling relevan dari COA.
   - Sertakan confidence dan alasan singkat untuk tiap pilihan.
   - Jangan hanya beri 1 jawaban benar — tujuannya untuk memberi alternatif bagi petugas koperasi.

---

### FORMAT OUTPUT WAJIB (JSON valid):
- Output **harus berupa JSON valid** (bisa di-parse tanpa error).
- **Jangan hapus field apa pun.**
- Jika data tidak ditemukan di teks, isi value dengan `null` (bukan hapus atau diganti dengan karakter lain).
- Selalu sertakan semua key dengan urutan seperti di bawah.

```json
{{
  "tanggal_transaksi": "<YYYY-MM-DD>",
  "pihak_terlibat": "<Nama vendor atau pihak terkait>",
  "deskripsi_transaksi": "<Deskripsi ringkas transaksi>",
  "mata_uang": "<Kode mata uang, mis. IDR>",
  "nominal_total": "<Total nominal transaksi>",
  "tipe_transaksi": "<debit|kredit>",
  "items": [
    {{
      "nama_item": "<Nama item>",
      "jumlah": "<Jumlah>",
      "harga_satuan": "<Harga satuan>",
      "subtotal": "<Jumlah × harga_satuan>"
    }}
  ],
  "rekomendasi_akun_transaksi": [
    {{
      "kode_akun": "<Kode akun>",
      "nama_akun": "<Nama akun>",
      "confidence": "<Nilai antara 0–1>",
      "alasan": "<Alasan rekomendasi>"
    }}
  ]
}}
"""


def analyze_receipt_with_llm(ocr_json):
    if not GROQ_API_KEY:
        raise RuntimeError('GROQ_API_KEY is not set in environment')

    client = Groq(api_key=GROQ_API_KEY)

    prompt = PROMPT_TEMPLATE.format(ocr=ocr_json)

    response = client.chat.completions.create(
        model='llama-3.3-70b-versatile',
        messages=[
            {'role': 'system', 'content': 'Kamu adalah ahli akuntansi analisis yang membantu menganalisis bukti transaksi keuangan'},
            {'role': 'user', 'content': prompt}
        ],
        temperature=0,
        response_format={"type": "json_object"}
    )

    # pastikan hasil LLM dikonversi ke dict
    llm_content = response.choices[0].message.content
    if isinstance(llm_content, str):
        import json
        try:
            llm_content = json.loads(llm_content)
        except json.JSONDecodeError:
            llm_content = {"raw": llm_content}

    return llm_content