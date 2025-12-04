@extends('layouts.app')

@section('page_title', 'Rekomendasi Kode Akun')

@section('content')
<div class="container py-4">

    {{-- STEP INDICATOR  --}}
    <div class="steps d-flex justify-content-between mb-5 position-relative">
        
        {{-- STEP 1 --}}
        <div class="step-item {{ $step > 1 ? 'completed' : ($step == 1 ? 'current' : '') }}">
            <div class="step-circle">1</div>
            <div class="step-title">Unggah File</div>
        </div>

        {{-- STEP 2 --}}
        <div class="step-item {{ $step > 2 ? 'completed' : ($step == 2 ? 'current' : '') }}">
            <div class="step-circle">2</div>
            <div class="step-title">Proses AI</div>
        </div>

        {{-- STEP 3 --}}
        <div class="step-item {{ $step == 3 ? 'current' : '' }}">
            <div class="step-circle">3</div>
            <div class="step-title">Hasil</div>
        </div>

    </div>


    {{-- STEP 1: UNGGAH FILE --}}
    @if($step == 1)
        <div class="row justify-content-center">
            <div class="col-12">
                <form action="{{ route('account-code-recommender.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                    @csrf 
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <h5 class="fw-bold text-dark">Unggah Bukti Transaksi</h5>
                                <p class="text-muted small">Upload foto struk, nota, atau kuitansi (JPG/PNG)</p>
                            </div>
                            <div id="dropZone" class="dropzone-box text-center mb-4">
                                <div class="py-5">
                                    <i class="bi bi-cloud-arrow-up text-primary fs-1 mb-3 d-block"></i>
                                    <p class="fw-bold mb-1 text-dark">Drag & Drop file di sini</p>
                                    <p class="text-muted small mb-3">atau</p>
                                    <button type="button" id="browseBtn" class="btn btn-outline-primary rounded-pill px-4">
                                        Pilih File dari Komputer
                                    </button>
                                    <p class="mt-3 text-muted" style="font-size: 0.75rem;">Maksimal ukuran file: 10MB</p>
                                </div>
                            </div>
                            <input type="file" name="document" id="fileInput" class="d-none" accept=".jpg,.jpeg,.png">
                            <div id="uploadedFileArea" class="mb-4"></div>
                            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                                <button type="button" class="btn btn-light text-muted border px-4" id="cancelBtn" style="display: none;">Batal</button>
                                <button type="submit" class="btn btn-primary px-4" id="uploadBtn" disabled>Unggah</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif


    {{-- STEP 2: LOADING SCREEN --}}
    @if($step == 2)
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-3 text-center py-5">
                    <div class="card-body">
                        <div class="spinner-border text-primary mb-4" role="status" style="width: 3rem; height: 3rem;"></div>
                        <h5 class="fw-bold text-dark">Sedang Menganalisis...</h5>
                        <p class="text-muted mb-0">AI sedang memahami data dan menentukan nomor akun yang tepat.</p>
                        <p class="text-muted small fst-italic mt-2">Mohon tunggu sejenak, jangan tutup halaman ini.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- STEP 3: HASIL & VERIFIKASI --}}
    @if($step == 3)
        <form action="{{ route('recommender.save') }}" method="POST" id="saveForm">
            @csrf
            <div class="row align-items-stretch g-4"> 
                
                {{-- Panel Kiri: Gambar Bukti --}}
                <div class="col-lg-5">
                    <div class="card shadow-sm border-0 h-100 rounded-3">
                        <div class="card-header bg-white py-3 fw-bold border-bottom text-dark">
                            Bukti Transaksi
                        </div>
                        <div class="card-body bg-white text-center d-flex align-items-start justify-content-center p-3 image-scroll-area">
                            @if(isset($image_url))
                                <img src="{{ $image_url }}" class="img-fluid rounded shadow-sm" alt="Preview Bukti" style="width: 100%; object-fit: contain;">
                            @else
                                <p class="text-muted my-auto">Gambar tidak ditemukan.</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Panel Kanan: Form Input --}}
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0 h-100 rounded-3">
                        <div class="card-header bg-white py-3 fw-bold border-bottom">
                            Rekomendasi Kode Akun
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            
                            {{-- Info Transaksi --}}
                            <div class="mb-3">
                                <label class="form-label small text-muted text-uppercase fw-bold">Analisis AI</label>
                                <textarea class="form-control bg-light" name="analisis_ai" rows="2" placeholder="Uraian transaksi..." disabled>{{ $result['analisis_akuntansi'] ?? ($result['pihak_terlibat'] ?? '') }}</textarea>
                            </div>

                            {{-- Baris Input Data (Nomor Bukti, Tanggal, Jenis) --}}
                            <div class="row g-3 mb-3">
                                {{-- 1. Nomor Bukti --}}
                                <div class="col-md-4">
                                    <label class="form-label small text-muted text-uppercase fw-bold">No. Bukti</label>
                                    <input type="number" class="form-control" name="nomor_bukti" 
                                           value="{{ $result['nomor_bukti'] ?? '' }}" 
                                           placeholder="Auto (Kosongkan)" min="1">
                                    <div class="form-text text-end fst-italic" style="font-size: 0.7rem;">
                                        *Jika ada di bukti
                                    </div>
                                </div>

                                {{-- 2. Tanggal --}}
                                <div class="col-md-4">
                                    <label class="form-label small text-muted text-uppercase fw-bold">Tanggal</label>
                                    <input type="date" class="form-control" name="tanggal" 
                                           value="{{ $result['tanggal_transaksi'] ?? date('Y-m-d') }}">
                                </div>

                                {{-- 3. Jenis Bukti --}}
                                <div class="col-md-4">
                                    <label class="form-label small text-muted text-uppercase fw-bold">Jenis Bukti</label>
                                    <select class="form-select" name="jenis_bukti" id="jenisBuktiSelect">
                                        <option value="BKK">BKK (Kas Keluar)</option>
                                        <option value="BKM">BKM (Kas Masuk)</option>
                                        <option value="BM">BM (Memorial)</option>
                                        <option value="NK_BELI">Nota Pembelian</option>
                                        <option value="NK_JUAL">Nota Penjualan</option>
                                    </select>
                                </div>
                            </div>

                            <hr class="text-muted opacity-25">

                            {{-- Header Tabel --}}
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label small text-muted text-uppercase fw-bold">Rincian Akun</label>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="addRowBtn">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Baris
                                </button>
                            </div>

                            {{-- Tabel Jurnal --}}
                            <div class="table-responsive custom-scroll-area mb-3 rounded-3">
                                <table class="table table-borderless table-sm mb-0 align-middle">
                                    <thead class="text-secondary small fw-bold border-bottom">
                                        <tr>
                                            <th style="width: 45%" class="ps-3 py-2">Nama Akun</th>
                                            <th style="width: 25%" class="text-end py-2">Debit (Rp)</th>
                                            <th style="width: 25%" class="text-end py-2">Kredit (Rp)</th>
                                            <th style="width: 5%" class="py-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="journalBody">
                                        {{-- Baris akan di-generate via Javascript --}}
                                    </tbody>
                                </table>
                            </div>

                            {{-- Footer Total --}}
                            <div class="mt-auto">
                                <div class="row g-2 align-items-center fw-bold border-top pt-2">
                                    <div class="col-6 text-end text-uppercase small text-muted">Total</div>
                                    <div class="col-3 text-end text-dark" id="totalDebitDisplay">0</div>
                                    <div class="col-3 text-end text-dark" id="totalCreditDisplay">0</div>
                                </div>

                                {{-- Indikator Balance --}}
                                <div id="balanceAlert" class="alert d-flex align-items-center py-2 mt-3 mb-0 border-0 bg-light text-muted small rounded-3">
                                    <i class="bi me-2" id="balanceIcon"></i>
                                    <strong id="balanceText">Menunggu input...</strong>
                                </div>
                            </div>

                        </div>
                        
                        {{-- Action Buttons --}}
                        <div class="card-footer bg-white text-end py-3 border-top">
                            <a href="{{ route('account-code-recommender.show', 1) }}" class="btn btn-light border me-2">Batalkan</a>
                            <button type="button" class="btn btn-primary px-4" id="saveButton" disabled>Simpan Transaksi</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif

</div>
@endsection

@push('styles')
<style>
    .steps { margin-bottom: 3rem; position: relative; max-width: 600px; margin-left: auto; margin-right: auto; }
    .steps::before { 
        content: ""; position: absolute; top: 15px; left: 0; width: 100%; height: 2px; 
        background: #e9ecef; z-index: 0; 
    }
    .step-item { 
        position: relative; z-index: 1; background: #f8fafc; padding: 0 10px; 
        text-align: center; display: flex; flex-direction: column; align-items: center;
    }
    .step-circle { 
        width: 32px; height: 32px; line-height: 32px; border-radius: 50%; 
        background: #e9ecef; color: #6c757d; font-weight: bold; margin-bottom: 5px; 
        transition: all 0.3s;
        display: flex; align-items: center; justify-content: center;
    }
    .step-title { font-size: 0.85rem; color: #6c757d; font-weight: 500; white-space: nowrap; }
    
    .step-item.completed .step-circle { 
        background: #198754; color: white; 
    }
    .step-item.completed .step-title { color: #198754; font-weight: bold; }

    .step-item.current .step-circle { 
        background: #0d6efd; color: white; 
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2);
    }
    .step-item.current .step-title { color: #0d6efd; font-weight: bold; }

    .custom-scroll-area { 
        height: 170px; 
        overflow-y: auto;
        scrollbar-width: thin;
        border: 1px solid #dee2e6;
        background-color: #fff;
    }
    .custom-scroll-area thead th {
        position: sticky; top: 0; background-color: #fff; z-index: 5; box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .custom-scroll-area::-webkit-scrollbar { width: 6px; }
    .custom-scroll-area::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scroll-area::-webkit-scrollbar-thumb { background-color: #c1c1c1; border-radius: 4px; }
    .custom-scroll-area::-webkit-scrollbar-thumb:hover { background-color: #a8a8a8; }

    .image-scroll-area {
        height: 600px; 
        overflow-y: auto;
    }
    
    .dropzone-box { border: 2px dashed #ced4da; background-color: #f8f9fa; border-radius: 12px; transition: all 0.2s ease; cursor: pointer; }
    .dropzone-box:hover, .drop-zone-over { border-color: #0d6efd; background-color: #f1f7ff; }
</style>
@endpush

@push('scripts')
<script>
    function notify(msg, type='success') {
        if(typeof window.showToast === 'function') window.showToast(msg, type);
        else alert(msg);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const currentStep = {{ $step ?? 1 }};
        if(currentStep === 1) setupFileUpload();
        if(currentStep === 2) processAI();
        if(currentStep === 3) setupDynamicForm();
    });

    // --- LOGIC STEP 1 ---
    function setupFileUpload() { 
        const fileInput = document.getElementById('fileInput');
        const browseBtn = document.getElementById('browseBtn');
        const uploadButton = document.getElementById('uploadBtn');
        const cancelButton = document.getElementById('cancelBtn');
        const dropZone = document.getElementById('dropZone');
        const uploadedFileArea = document.getElementById('uploadedFileArea');
        
        if (!dropZone) return;

        const resetForm = () => {
            fileInput.value = '';
            uploadedFileArea.innerHTML = '';
            uploadButton.disabled = true;
            cancelButton.style.display = 'none';
        };

        browseBtn.addEventListener('click', (e) => { 
            e.preventDefault(); e.stopPropagation(); fileInput.click(); 
        });

        dropZone.addEventListener('click', () => fileInput.click()); 
        
        cancelButton.addEventListener('click', (e) => { e.preventDefault(); resetForm(); });

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!validTypes.includes(file.type)) { notify("Format salah. Gunakan JPG/PNG.", 'error'); resetForm(); return; }
                if (file.size > 10 * 1024 * 1024) { notify("Ukuran file maks 10MB.", 'error'); resetForm(); return; }
                
                uploadedFileArea.innerHTML = `
                    <div class="alert alert-light border shadow-sm d-flex align-items-center justify-content-between p-3 rounded-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-2 me-3"><i class="bi bi-file-earmark-image fs-4"></i></div>
                            <div><h6 class="mb-0 text-dark fw-bold text-truncate" style="max-width: 250px;">${file.name}</h6><small class="text-muted">${(file.size / 1024 / 1024).toFixed(2)} MB</small></div>
                        </div>
                        <button type="button" class="btn-close" id="removeFileBtn" aria-label="Hapus"></button>
                    </div>`;

                document.getElementById('removeFileBtn').addEventListener('click', (e) => { e.stopPropagation(); resetForm(); });
                uploadButton.disabled = false;
                cancelButton.style.display = 'block';
            } else { resetForm(); }
        });

        ['dragenter', 'dragover'].forEach(e => dropZone.addEventListener(e, (ev) => { ev.preventDefault(); ev.stopPropagation(); dropZone.classList.add('drop-zone-over'); }));
        ['dragleave', 'drop'].forEach(e => dropZone.addEventListener(e, (ev) => { ev.preventDefault(); ev.stopPropagation(); dropZone.classList.remove('drop-zone-over'); }));
        
        dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            fileInput.files = dt.files;
            fileInput.dispatchEvent(new Event('change'));
        });
    }

    // --- LOGIC STEP 2 ---
    function processAI() {
        fetch('{{ route("recommender.process") }}', {
            method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') window.location.href = '{{ route("account-code-recommender.show", 3) }}';
            else { notify(data.message || "Gagal memproses.", 'error'); setTimeout(() => window.location.href = '{{ route("account-code-recommender.show", 1) }}', 2500); }
        })
        .catch(() => { notify("Error server.", 'error'); setTimeout(() => window.location.href = '{{ route("account-code-recommender.show", 1) }}', 2500); });
    }

    // --- LOGIC STEP 3 ---
    function setupDynamicForm() {
        let accountOptionsHtml = '<option value="">-- Pilih Akun --</option>';
        @if(isset($groupedAccounts))
            @foreach($groupedAccounts as $groupName => $accounts)
                accountOptionsHtml += '<optgroup label="{{ $groupName }}">';
                @foreach($accounts as $acc)
                    accountOptionsHtml += '<option value="{{ $acc->code }}">{{ $acc->code }} - {{ $acc->name }}</option>';
                @endforeach
                accountOptionsHtml += '</optgroup>';
            @endforeach
        @endif

        const tbody = document.getElementById('journalBody');
        const addBtn = document.getElementById('addRowBtn');
        const totalDebitEl = document.getElementById('totalDebitDisplay');
        const totalCreditEl = document.getElementById('totalCreditDisplay');
        const balanceAlert = document.getElementById('balanceAlert');
        const balanceText = document.getElementById('balanceText');
        const balanceIcon = document.getElementById('balanceIcon');
        const saveBtn = document.getElementById('saveButton');
        const jenisBuktiSelect = document.getElementById('jenisBuktiSelect');

        function addRow(accountCode = '', debit = 0, credit = 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="ps-3">
                    <select class="form-select form-select-sm" name="accounts[]" required>${accountOptionsHtml}</select>
                </td>
                <td><input type="number" class="form-control form-control-sm text-end debit-input" name="debit[]" value="${debit}" min="0" step="any"></td>
                <td><input type="number" class="form-control form-control-sm text-end credit-input" name="credit[]" value="${credit}" min="0" step="any"></td>
                <td class="text-center align-middle">
                    <button type="button" class="btn-close remove-row" aria-label="Hapus" style="font-size: 0.75rem;"></button>
                </td>
            `;
            tbody.appendChild(tr);

            if(accountCode) {
                const select = tr.querySelector('select');
                Array.from(select.options).forEach(opt => { if(opt.value == accountCode) select.value = accountCode; });
            }
            calculateTotal();
        }

        tbody.addEventListener('click', (e) => {
            const btn = e.target.closest('.remove-row');
            if (btn) {
                btn.closest('tr').remove();
                if (tbody.children.length === 0) { notify("Minimal 1 baris.", 'error'); addRow(); }
                calculateTotal();
            }
        });

        tbody.addEventListener('input', (e) => {
            if (e.target.classList.contains('debit-input') || e.target.classList.contains('credit-input')) calculateTotal();
        });

        function calculateTotal() {
            let sumDebit = 0; let sumCredit = 0;
            document.querySelectorAll('.debit-input').forEach(i => sumDebit += parseFloat(i.value) || 0);
            document.querySelectorAll('.credit-input').forEach(i => sumCredit += parseFloat(i.value) || 0);

            const fmt = (n) => n.toLocaleString('id-ID', {minimumFractionDigits: 2});
            totalDebitEl.textContent = fmt(sumDebit);
            totalCreditEl.textContent = fmt(sumCredit);

            const diff = Math.abs(sumDebit - sumCredit);
            if (sumDebit > 0 && diff < 1) { 
                balanceAlert.className = "alert d-flex align-items-center py-2 mt-3 mb-0 border-0 bg-success bg-opacity-10 text-success small rounded-3";
                balanceIcon.className = "bi bi-check-circle-fill me-2";
                balanceText.textContent = "Balance (Seimbang). Siap disimpan.";
                saveBtn.disabled = false;
            } else {
                balanceAlert.className = "alert d-flex align-items-center py-2 mt-3 mb-0 border-0 bg-danger bg-opacity-10 text-danger small rounded-3";
                balanceIcon.className = "bi bi-exclamation-triangle-fill me-2";
                balanceText.textContent = `Tidak Seimbang. Selisih: Rp ${fmt(diff)}`;
                saveBtn.disabled = true;
            }
        }

        const aiResult = @json($result ?? []); 
        if (aiResult.jenis_bukti) {
            const map = { 'bkk': 'BKK', 'bkm': 'BKM', 'bm': 'BM', 'pembelian': 'NK_BELI', 'penjualan': 'NK_JUAL' };
            const key = aiResult.jenis_bukti.toLowerCase();
            for(let k in map) { if(key.includes(k)) jenisBuktiSelect.value = map[k]; }
        }

        if (aiResult.rekomendasi_akun && aiResult.rekomendasi_akun.length > 0) {
            aiResult.rekomendasi_akun.forEach(rec => {
                let nom = parseFloat(rec.nominal) || 0;
                let deb = (rec.posisi?.toLowerCase() === 'debit') ? nom : 0;
                let cre = (rec.posisi?.toLowerCase() === 'kredit') ? nom : 0;
                addRow(rec.kode_akun, deb, cre);
            });
        } else { addRow(); addRow(); }

        addBtn.addEventListener('click', () => addRow());

        saveBtn.addEventListener('click', () => {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
            const formData = new FormData(document.getElementById('saveForm'));
            fetch('{{ route("recommender.save") }}', {
                method: 'POST', body: formData, headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(d => {
                if(d.status === 'success') { notify("Berhasil disimpan!", 'success'); setTimeout(() => window.location.href = '{{ route("account-code-recommender.show", 1) }}', 1000); }
                else { notify(d.message, 'error'); saveBtn.disabled = false; saveBtn.textContent = 'Simpan Transaksi'; }
            })
            .catch(() => { notify("Gagal server.", 'error'); saveBtn.disabled = false; saveBtn.textContent = 'Simpan Transaksi'; });
        });
    }
</script>
@endpush