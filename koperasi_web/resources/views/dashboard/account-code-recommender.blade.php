@extends('layouts.app')

@section('page_title', 'Rekomendasi Kode Akun')

@section('content')
<div class="container py-4">

    {{-- STEP INDICATOR (Tidak berubah) --}}
    <div class="steps d-flex justify-content-between">
        <div class="step-item {{ $step >= 1 ? 'active' : '' }} {{ $step == 1 ? 'current' : '' }}">
            <div class="step-number">1</div>
            <div class="step-title">Unggah File</div>
        </div>
        <div class="step-item {{ $step >= 2 ? 'active' : '' }} {{ $step == 2 ? 'current' : '' }}">
            <div class="step-number">2</div>
            <div class="step-title">Memproses</div>
        </div>
        <div class="step-item {{ $step >= 3 ? 'active' : '' }} {{ $step == 3 ? 'current' : '' }}">
            <div class="step-number">3</div>
            <div class="step-title">Hasil</div>
        </div>
    </div>

    {{-- SECTION 1 (Tidak berubah) --}}
    @if($step == 1)
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <form action="{{ route('account-code-recommender.store') }}" method="POST" enctype="multipart/form-data">
            @csrf 
            <div class="card shadow-sm">
                <div class="card-body text-center py-5" id="dropZone">
                    <i class="bi bi-cloud-arrow-up fs-1 text-secondary mb-3"></i>
                    <p class="mb-1">Letakkan dokumen Anda di sini, atau<a href="#"  id="browseLink"> klik untuk memilih berkas</a></p>
                    <small class="text-secondary d-block mb-3">Format yang didukung: .jpg, .png, .jpeg | Maksimal 10 MB</small>
                    <input type="file" name="document" id="fileInput" class="d-none">
                    <p id="fileName" class="mt-2 fw-bold"></p>
                    <button type="submit" class="btn btn-primary mt-3" id="uploadButton" disabled>Unggah</button>
                </div>
            </div>
        </form>
    @endif

    {{-- SECTION 2 (Tidak berubah) --}}
    @if($step == 2)
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <div class="progress" style="height: 8px; max-width: 400px; margin: 1.5rem auto;">ssd
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <h5 class="card-title mb-2 mt-4">Memproses File</h5>
                <p class="text-muted mb-3">Sedang mengekstrak...</p>
            </div>
        </div>
    @endif

    {{-- SECTION 3 --}}
    @if($step == 3)
        @if ($result)
            
            <form action="{{ route('recommender.save') }}" method="POST" id="saveForm">
                @csrf
                <input type="hidden" name="selected_account_name" id="selectedAccountName">

                <div class="row">
                    <div class="col-md-6">
                        <h5>Sumber</h5>
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <img src="{{ $image_url }}" alt="Source Document" class="img-fluid w-100">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h5>{{ $image_url ? basename($image_url) : 'Hasil' }}</h5>

                        <div class="btn-group mb-3 w-100">
                            <button type="button" id="btnResult" class="btn btn-primary w-50">Hasil</button>
                            <button type="button" id="btnTable" class="btn btn-outline-secondary w-50">Lihat Tabel</button>
                        </div>

                        {{-- RESULT PANEL (Tidak berubah) --}}
                        <div id="resultPanel">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h6>Keterangan</h6>
                                    <div class="mb-3">
                                        <label class="form-label small">Deskripsi Transaksi</label>
                                        <input type="text" class="form-control" name="deskripsi"
                                               value="{{ $result['deskripsi_transaksi'] ?? 'Tidak ditemukan' }}">
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label class="form-label small">Tanggal Transaksi</label>
                                            <input type="text" class="form-control" name="tanggal"
                                                   value="{{ $result['tanggal_transaksi'] ?? 'N/A' }}">
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label class="form-label small">Tipe (Debit/Kredit)</label>
                                            <input type="text" class="form-control" name="tipe"
                                                   value="{{ $result['tipe_transaksi'] ?? 'N/A' }}">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small">Total</label>
                                        <input type="number" step="0.01" class="form-control" name="nominal_total"
                                               value="{{ $result['nominal_total'] ?? 0 }}">
                                    </div>
                                    <hr>
                                    <h6>Rekomendasi Nomor Akun</h6>
                                    <p class="small text-muted">*Silakan pilih nomor akun yang benar.</p>
                                    <div class="list-group">
                                        @forelse($result['rekomendasi_akun_transaksi'] ?? [] as $rec)
                                            <label class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong class="me-2">{{ $rec['kode_akun'] ?? 'N/A' }}</strong>
                                                    <span>{{ $rec['nama_akun'] ?? 'N/A' }}</span>
                                                </div>
                                                <input class="form-check-input" type="radio" name="selected_account"
                                                       value="{{ $rec['kode_akun'] ?? '' }}"
                                                       data-account-name="{{ $rec['nama_akun'] ?? 'N/A' }}"
                                                       {{ $loop->first ? 'checked' : '' }}>
                                            </label>
                                        @empty
                                            <p class="text-danger">Format rekomendasi tidak sesuai.</p>
                                        @endforelse
                                        <label class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong class="me-2">Input Manual</strong>
                                                <span>(Jika rekomendasi tidak sesuai)</span>
                                            </div>
                                            <input class="form-check-input" type="radio" name="selected_account" value="manual">
                                        </label>
                                    </div>
                                    <div id="manualInputFields" class="d-none mt-3 p-3 bg-light rounded border">
                                        <h6>Input Akun Manual</h6>
                                        <div class="mb-2">
                                            <label class="form-label small">Kode Akun</label>
                                            <input type="text" class="form-control" name="manual_account_code" placeholder="Contoh: 515">
                                        </div>
                                        <div>
                                            <label class="form-label small">Nama Akun</label>
                                            <input type="text" class="form-control" name="manual_account_name" placeholder="Contoh: Beban Lain-lain">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TABLE PANEL (Tidak berubah) --}}
                        <div id="tablePanel" class="d-none">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Nama Barang</th>
                                                <th>Harga</th>
                                                <th>Jumlah</th>
                                                <th style="width: 50px;"></th> 
                                            </tr>
                                        </thead>
                                        <tbody id="itemsTableBody">
                                            @forelse($result['items'] ?? [] as $item)
                                                <tr>
                                                    <td><input type="text" name="item_nama[]" class="form-control form-control-sm" value="{{ $item['nama_item'] ?? 'N/A' }}"></td>
                                                    <td><input type="number" name="item_harga[]" class="form-control form-control-sm" value="{{ $item['harga_satuan'] ?? '0' }}"></td>
                                                    <td><input type="number" name="item_jumlah[]" class="form-control form-control-sm" value="{{ $item['jumlah'] ?? '0' }}"></td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-sm delete-item-btn">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted p-4">
                                                        Tidak ada item ditemukan. Klik "Tambah Item" untuk memulai.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                    <button type="button" id="addItemRow" class="btn btn-sm btn-success mt-2">
                                        <i class="bi bi-plus"></i> Tambah Item Baru
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('account-code-recommender.show', 1) }}" class="btn btn-secondary">Batal</a>
                            <button type="button" class="btn btn-primary" id="saveButton">Simpan</button>
                        </div>
                    </div>
                </div>
            </form>

        @else
            <div class="alert alert-warning">
                Tidak ada hasil ditemukan. Silakan coba lagi.
                <a href="{{ route('account-code-recommender.show', ['step' => 1]) }}">Coba Lagi</a>.
            </div>
        @endif
    @endif

    
    @if($step == 3)
    <div class="modal fade" id="saveConfirmModal" tabindex="-1" aria-labelledby="saveConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="saveConfirmModalLabel">Konfirmasi Penyimpanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin data yang diinput sudah benar?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmSaveBtn">Ya, Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="saveSuccessModal" tabindex="-1" aria-labelledby="saveSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="saveSuccessModalLabel">Berhasil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Data transaksi telah berhasil disimpan.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="successModalOkBtn">Oke</button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

@push('styles')
<style>
    .drop-zone-over {
        border: 2px dashed #0d6efd !important;
        background-color: #f0f5ff;
    }
    .delete-item-btn {
        padding: 0.15rem 0.45rem;
        line-height: 1;
    }
</style>
@endpush

@push('scripts')
<script>
    function initializeApp() {
        // ... (Fungsi ini tidak berubah)
        const currentStep = {{ $step ?? 1 }};
        switch (currentStep) {
            case 1: setupCustomFileUpload(); break;
            case 2: aiProcessing(); break;
            case 3: viewResult(); break;
        }
    }

    function setupCustomFileUpload() {
        // ... (Fungsi ini tidak berubah)
        const browseLink = document.getElementById('browseLink');
        const fileInput = document.getElementById('fileInput');
        const fileNameDisplay = document.getElementById('fileName');
        const uploadButton = document.getElementById('uploadButton');
        const dropZone = document.getElementById('dropZone');
        if (!browseLink || !fileInput || !fileNameDisplay || !uploadButton || !dropZone) return; 
        browseLink.addEventListener('click', (e) => { e.preventDefault(); fileInput.click(); });
        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                fileNameDisplay.textContent = 'File selected: ' + fileInput.files[0].name;
                uploadButton.disabled = false;
            } else {
                fileNameDisplay.textContent = '';
                uploadButton.disabled = true;
            }
        });
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => { e.preventDefault(); e.stopPropagation(); }, false);
        });
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => { dropZone.classList.add('drop-zone-over'); }, false);
        });
        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => { dropZone.classList.remove('drop-zone-over'); }, false);
        });
        dropZone.addEventListener('drop', (e) => {
            fileInput.files = e.dataTransfer.files;
            fileInput.dispatchEvent(new Event('change'));
        }, false);
    }

    function aiProcessing() {
        // ... (Fungsi ini tidak berubah)
        fetch('{{ route("recommender.process") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        })
        .then(res => res.ok ? res.json() : Promise.reject("Pemrosesan gagal"))
        .then(data => {
            if (data.status === 'success') window.location.href = '{{ route("account-code-recommender.show", ["step" => 3]) }}';
            else { alert(data.message || 'Kesalahan'); window.location.href = '{{ route("account-code-recommender.show", ["step" => 1]) }}'; }
        })
        .catch(() => {
            alert('Terjadi kesalahan saat memproses berkas. Silakan coba lagi.');
            window.location.href = '{{ route("account-code-recommender.show", ["step" => 1]) }}';
        });
    }

    // [PERBAIKAN] Ganti SELURUH fungsi viewResult dengan ini
    function viewResult() {
        
        // --- 1. Logika Tabbing (Tidak berubah) ---
        const resultPanel = document.getElementById('resultPanel');
        const tablePanel = document.getElementById('tablePanel');
        const btnResult = document.getElementById('btnResult');
        const btnTable = document.getElementById('btnTable');
        btnResult.addEventListener('click', () => {
            resultPanel.classList.remove('d-none');
            tablePanel.classList.add('d-none');
            btnResult.classList.add('btn-primary');
            btnResult.classList.remove('btn-outline-secondary');
            btnTable.classList.remove('btn-primary');
            btnTable.classList.add('btn-outline-secondary');
        });
        btnTable.addEventListener('click', () => {
            resultPanel.classList.add('d-none');
            tablePanel.classList.remove('d-none');
            btnTable.classList.add('btn-primary');
            btnTable.classList.remove('btn-outline-secondary');
            btnResult.classList.remove('btn-primary');
            btnResult.classList.add('btn-outline-secondary');
        });

        // --- 2. Logika Hidden Input Rekomendasi (Tidak berubah) ---
        const selectedAccountNameInput = document.getElementById('selectedAccountName');
        const radios = document.querySelectorAll('input[name="selected_account"]');
        function updateHidden() {
            const checked = document.querySelector('input[name="selected_account"]:checked');
            if (checked && checked.value !== 'manual') { 
                selectedAccountNameInput.value = checked.dataset.accountName;
            } else {
                selectedAccountNameInput.value = '';
            }
        }
        updateHidden();
        radios.forEach(r => r.addEventListener('change', updateHidden));

        // --- 3. Logika Menampilkan Input Manual (Tidak berubah) ---
        const manualInputFields = document.getElementById('manualInputFields');
        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'manual') {
                    manualInputFields.classList.remove('d-none');
                } else {
                    manualInputFields.classList.add('d-none');
                }
            });
        });

        // --- 4. Logika Add/Delete Item Row (Tidak berubah) ---
        const tableBody = document.getElementById('itemsTableBody');
        const addItemButton = document.getElementById('addItemRow');
        function createNewRowHtml() {
            return `
                <tr>
                    <td><input type="text" name="item_nama[]" class="form-control form-control-sm" value=""></td>
                    <td><input type="number" name="item_harga[]" class="form-control form-control-sm" value="0"></td>
                    <td><input type="number" name="item_jumlah[]" class="form-control form-control-sm" value="1"></td>
                    <td><button type="button" class="btn btn-danger btn-sm delete-item-btn"><i class="bi bi-trash"></i></button></td>
                </tr>
            `;
        }
        addItemButton.addEventListener('click', () => {
            const noItemsRow = tableBody.querySelector('td[colspan="4"]');
            if (noItemsRow) {
                noItemsRow.parentElement.remove();
            }
            tableBody.insertAdjacentHTML('beforeend', createNewRowHtml());
        });
        tableBody.addEventListener('click', (event) => {
            const deleteButton = event.target.closest('.delete-item-btn');
            if (deleteButton) {
                deleteButton.closest('tr').remove();
            }
        });

        // --- [BARU] 5. Logika Pop-up Modal (AJAX) ---
        
        // Ambil elemen-elemen baru
        const saveForm = document.getElementById('saveForm');
        const saveButton = document.getElementById('saveButton');
        const confirmSaveBtn = document.getElementById('confirmSaveBtn');
        const successModalOkBtn = document.getElementById('successModalOkBtn');

        // Instance Modal Bootstrap (agar kita bisa kontrol Buka/Tutup)
        const confirmModal = new bootstrap.Modal(document.getElementById('saveConfirmModal'));
        const successModal = new bootstrap.Modal(document.getElementById('saveSuccessModal'));

        // Saat tombol "Save" utama diklik...
        saveButton.addEventListener('click', () => {
            // ...tampilkan modal konfirmasi
            confirmModal.show();
        });

        // Saat tombol "Ya, Simpan" di modal konfirmasi diklik...
        confirmSaveBtn.addEventListener('click', () => {
            // 1. Sembunyikan modal konfirmasi
            confirmModal.hide();
            
            // 2. Tunjukkan status loading di tombol
            confirmSaveBtn.textContent = 'Menyimpan...';
            confirmSaveBtn.disabled = true;

            // 3. Siapkan data form untuk AJAX
            const formData = new FormData(saveForm);
            
            // 4. Kirim data via fetch (AJAX)
            fetch('{{ route("recommender.save") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}', // Ambil token dari form
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // 5a. Jika SUKSES, tampilkan modal berhasil
                    successModal.show();
                } else {
                    // 5b. Jika GAGAL (misal: validasi), tampilkan alert error
                    alert('Error: ' + (data.message || 'Gagal menyimpan data.'));
                }
            })
            .catch(error => {
                // 5c. Tangani error server
                console.error('Kesalahan Saat Menyimpan:', error);
                alert('Terjadi error pada server. Silakan coba lagi.');
            })
            .finally(() => {
                // 6. Kembalikan tombol konfirmasi ke normal
                confirmSaveBtn.textContent = 'Ya, Simpan';
                confirmSaveBtn.disabled = false;
            });
        });

        // Saat tombol "OK" di modal berhasil diklik...
        successModalOkBtn.addEventListener('click', () => {
            // ...redirect kembali ke Step 1
            window.location.href = '{{ route("account-code-recommender.show", ["step" => 1]) }}';
        });
    }

    document.addEventListener('DOMContentLoaded', initializeApp);
</script>
@endpush
@endsection