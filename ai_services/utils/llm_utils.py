import json
import os
from langchain_openai import ChatOpenAI
from langchain_core.prompts import ChatPromptTemplate
from langchain_core.output_parsers import JsonOutputParser
from langchain_core.exceptions import OutputParserException
    
# 1. Konfigurasi Path
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
COA_PATH = os.path.join(BASE_DIR, 'base_knowledge', 'coa.json')

# 2. Fungsi Load COA (OPTIMIZED: Filter & Format di Python)
def load_coa_optimized():
    try:
        with open(COA_PATH, 'r') as f:
            data = json.load(f)
        
        clean_list = []
        for akun in data:
            nomor = str(akun.get('nomor', ''))
            nama = akun.get('nama', 'Unknown')
            if len(nomor) >= 3:
                clean_list.append(f"- Kode: {nomor} | Nama: {nama}")
                
        if not clean_list:
            return "Warning: COA kosong."
        return "\n".join(clean_list)

    except Exception:
        return "Error loading COA."

# 3. Fungsi Utama
def analyze_ocr_transaction(ocr_input):
    coa_clean_text = load_coa_optimized()
    
    if isinstance(ocr_input, (dict, list)):
        ocr_text = json.dumps(ocr_input, indent=2, ensure_ascii=False)
    else:
        ocr_text = str(ocr_input)

    system_prompt = """
    Anda adalah Akuntan Internal Koperasi.
    Fokus perspektif Anda adalah sebagai PEMBELI/PENGGUNA JASA, kecuali dokumen jelas menyatakan Koperasi sebagai penerbit.

    [SUMBER KEBENARAN - DAFTAR AKUN]
    {coa_context}

    [TUGAS 1: KLASIFIKASI JENIS BUKTI]
    1. Analisis konteks teks dan tentukan `jenis_bukti` dari pilihan berikut:
       - **BKM (Bukti Kas Masuk)**: Jika koperasi MENERIMA uang (Kwitansi masuk, Bukti Setor).
       - **BKK (Bukti Kas Keluar)**: Jika koperasi MENGELUARKAN uang (Nota belanja, Kwitansi pembayaran beban).
       - **Nota Penjualan**: Jika dokumen adalah rincian penjualan barang/jasa kepada anggota/pelanggan.
       - **Nota Pembelian**: Jika dokumen adalah rincian pembelian stok/barang dari supplier.
       - **BM (Bukti Memorial)**: Jika transaksi non-tunai (Penyusutan, Pembalik, Koreksi).
    2. Jika terdapat nomor transaksi di dalamnya maka cantumkan sesuai yang tertera pada `nomor_bukti` dan jika tidak ada maka berikan `null` saja.


    [ATURAN MAPPING AKUN]
    1. **COPY-PASTE ONLY**: Nama akun di output HARUS SAMA PERSIS karakter-per-karakter dengan daftar di atas.
    2. **ATURAN AKUN YANG DIPILIH**:          
       - HANYA gunakan akun **Level Detail (3 Digit)** atau akun yang memiliki `kode_jenis` tidak null.          
       - **DILARANG KERAS** menggunakan akun Header/Induk (1 digit atau 2 digit) seperti "1 - ASET" atau "10 - Aset Lancar".          
       - Contoh BENAR: "101", "527".          
       - Contoh SALAH: "1", "10", "5", "52".
    2. **JANGAN HALUSINASI**:
       - Dilarang menggunakan akun yang tidak ada di daftar akun teks di atas.
       - Dilarang memodifikasi informasi-informasi dari akun termasuk memodifikasi NAMA AKUN.
    3. **PRINSIP DUALITAS (DOUBLE ENTRY) - WAJIB:**
       - Setiap transaksi HARUS memiliki minimal satu akun posisi **Debit** dan satu akun posisi **Kredit**.
       - **Total Nominal DEBIT harus SAMA PERSIS dengan Total Nominal KREDIT (Balance).**
       - Dilarang memberikan jurnal yang pincang (hanya Debit saja atau Kredit saja)
       - Dilarang memberikan jurnal kode akun dengan nominal 0.

    [ATURAN BAKU]
    - Aset → Debit menaikkan, Kredit menurunkan.
    - Kewajiban → Debit menurunkan, Kredit menaikkan.
    - Modal → Debit menaikkan, Kredit menurunkan.
    - Pendapatan → Debit menurunkan, Kredit menaikkan.
    - Biaya → Debit menurunkan, Kredit menaikkan.
    - HANYA catat nilai transaksi bersih (Total Bayar). 
    - JANGAN mencatat uang kembalian sebagai jurnal terpisah.
    - Balance: Total Debit == Total Kredit.

    Output HANYA JSON valid.
    """

    human_prompt = """
    Analisis data OCR berikut:
    {ocr_data}

    Berikan output JSON sesuai format:
    {{
      "jenis_bukti": "...",
      "nomor_bukti": "...",
      "tanggal_transaksi": "YYYY-MM-DD",
      "pihak_terlibat": "...",
      "items": [ {{ "nama_item": "...", "jumlah": 0, "harga": 0, "subtotal": 0 }} ],
      "analisis_akuntansi": "...",
      "rekomendasi_akun": [
         {{ "kode_akun": "...", "nama_akun": "...", "posisi": "Debit", "nominal": 0 }},
         {{ "kode_akun": "...", "nama_akun": "...", "posisi": "Kredit", "nominal": 0 }}
      ]
    }}
    """

    prompt = ChatPromptTemplate.from_messages([
        ("system", system_prompt),
        ("human", human_prompt)
    ])

    # llm = ChatGroq(
    #     temperature=0, 
    #     model_name="llama-3.3-70b-versatile"
    # )

    llm = ChatOpenAI(
        temperature=0,
        model="gpt-4o-mini", 
        request_timeout=600  
    )

    parser = JsonOutputParser()

    chain = prompt | llm | parser

    try:
        response = chain.invoke({
            "coa_context": coa_clean_text,
            "ocr_data": ocr_text
        })
        return response

    except OutputParserException as e:
        return {"status": "error", "message": "Gagal parsing JSON dari AI", "raw_output": str(e)}
    except Exception as e:
        return {"status": "error", "message": f"System Error: {str(e)}"}