<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Nomor Telepon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    
    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .navbar-logo {
            height: 40px;
            width: auto;
            object-fit: contain;
        }

        .divider {
            height: 40px;
            width: 1px;
            background-color: #dee2e6;
            margin: 0 1rem;
        }

        .form-control {
            border-radius: 8px;
            padding: 12px;
            border: 1px solid #ced4da;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(13,110,253,.15);
        }

        .btn-primary {
            padding: 12px 24px;
            border-radius: 8px;
        }

        .modal-content {
            border: none;
            border-radius: 15px;
        }

        @media (max-width: 480px) {
            .navbar-logo {
                height: 35px;
            }
            
            .divider {
                margin: 0 0.5rem;
            }
        }
    </style>
</head>
<body>
<div class="container py-4">
    <!-- Logo Header -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-8">
            <div class="d-flex justify-content-start align-items-center">
                <div class="d-flex align-items-center">
                    <img src="{{ asset('images/logors.png') }}" 
                         alt="Logo Rumah Sakit" 
                         class="navbar-logo"
                         style="height: 45px;">
                    
                    <div class="divider"></div>
                    
                    <img src="{{ asset('images/logo.png') }}" 
                         alt="Logo Peruri" 
                         class="navbar-logo"
                         style="height: 35px; margin-top: -10px;">
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-8">
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>Informasi!</strong>
                </div>
                <hr>
                <p class="mb-0">Masukkan nomor telepon untuk mencari data registrasi Anda.</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body p-4">
                    <h4 class="card-title mb-4">Cari Data Registrasi</h4>
                    <form id="phoneForm">
                        @csrf
                        <div class="mb-3">
                            <label for="phone" class="form-label">Nomor Telepon</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                <input type="tel" 
                                       class="form-control" 
                                       id="phone" 
                                       name="phone" 
                                       placeholder="Contoh: 0812XXXXXXX" 
                                       autocomplete="tel"
                                       pattern="[0-9]{10,13}"
                                       title="Masukkan nomor telepon yang valid (10-13 digit)"
                                       required>
                            </div>
                            <div class="form-text">Format: 08xxxxxxxxxx (10-13 digit)</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-2"></i>Cari Data
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Data -->
<div class="modal fade" id="phoneModal" tabindex="-1" aria-labelledby="phoneModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="phoneModalLabel">Data Registrasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modalContent">
                    <!-- Data akan diisi melalui JavaScript -->
                </div>
                <form id="hiddenForm">
                    <!-- Hidden inputs akan diisi melalui JavaScript -->
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btnSubmit">Konfirmasi Data</button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="mb-2">Mohon Tunggu</h5>
                <p class="text-muted mb-0">Sedang mencari data...</p>
            </div>
        </div>
    </div>
</div>

<!-- Script -->
<script>
$(document).ready(function () {
    // Debug flag
    const DEBUG = true;
    
    function debugLog(message, data = null) {
        if (DEBUG) {
            console.log(`[Debug] ${message}`, data || '');
        }
    }

    // Inisialisasi modal
    const loadingModal = document.getElementById('loadingModal');
    const phoneModal = document.getElementById('phoneModal');
    
    debugLog('Initializing modals');
    
    // Inisialisasi Bootstrap modal
    const bsLoadingModal = new bootstrap.Modal(loadingModal, {
        backdrop: 'static',
        keyboard: false
    });
    const bsPhoneModal = new bootstrap.Modal(phoneModal);

    // Fungsi untuk membersihkan modal
    function cleanupModals() {
        debugLog('Cleaning up modals');
        try {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
            debugLog('Modal cleanup completed');
        } catch (error) {
            console.error('Error during modal cleanup:', error);
        }
    }

    // Handle form submit
    $('#phoneForm').on('submit', function (e) {
        e.preventDefault();
        debugLog('Form submitted');
        
        // Reset dan bersihkan
        $('#modalContent').empty();
        $('#hiddenForm').empty();
        cleanupModals();
        
        // Tampilkan loading
        try {
            debugLog('Showing loading modal');
            bsLoadingModal.show();
        } catch (error) {
            console.error('Error showing loading modal:', error);
        }
        
        let phone = $('#phone').val();
        debugLog('Phone number:', phone);

        $.ajax({
            url: "{{ route('phone.fetch') }}",
            method: "POST",
            data: {
                phone: phone,
                _token: "{{ csrf_token() }}"
            },
            beforeSend: function() {
                debugLog('Starting AJAX request');
            },
            success: function (response) {
                debugLog('AJAX success response:', response);
                
                try {
                    // Pastikan loading modal dihentikan sebelum menampilkan data
                    if (loadingModal.classList.contains('show')) {
                        debugLog('Force closing loading modal');
                        bsLoadingModal.hide();
                        cleanupModals();
                    }
                    
                    if (response.status === 'success' && response.data) {
                        let user = response.data;
                        debugLog('User data received:', user);
                        
                        // Update modal content
                        $('#modalContent').html(`
                            <div class="row g-4">
                                <!-- Informasi Pribadi -->
                                <div class="col-12 mb-2">
                                    <h6 class="border-bottom pb-2 text-primary"><i class="bi bi-person-circle"></i> Informasi Pribadi</h6>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-person-vcard text-primary me-2 mt-1"></i>
                                        <div>
                                            <small class="text-muted d-block">No. KTP</small>
                                            <span class="fw-medium">${user.ktp || '-'}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-person text-primary me-2 mt-1"></i>
                                        <div>
                                            <small class="text-muted d-block">Nama</small>
                                            <span class="fw-medium">${user.name || '-'}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-geo text-primary me-2 mt-1"></i>
                                        <div>
                                            <small class="text-muted d-block">Tempat Lahir</small>
                                            <span class="fw-medium">${user.place_of_birth || '-'}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-calendar text-primary me-2 mt-1"></i>
                                        <div>
                                            <small class="text-muted d-block">Tanggal Lahir</small>
                                            <span class="fw-medium">${user.date_of_birth || '-'}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-gender-ambiguous text-primary me-2 mt-1"></i>
                                        <div>
                                            <small class="text-muted d-block">Jenis Kelamin</small>
                                            <span class="fw-medium">${user.gender === 'M' ? 'Laki-laki' : 'Perempuan'}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-geo-alt text-primary me-2 mt-1"></i>
                                        <div>
                                            <small class="text-muted d-block">Alamat</small>
                                            <span class="fw-medium">${user.address || '-'}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informasi Pekerjaan -->
                                <div class="col-12 mt-4 mb-2">
                                    <h6 class="border-bottom pb-2 text-primary"><i class="bi bi-briefcase"></i> Informasi Pekerjaan</h6>
                                </div>
                                
                                <div class="col-12">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-building text-primary me-2 mt-1"></i>
                                        <div>
                                            <small class="text-muted d-block">Unit Organisasi</small>
                                            <span class="fw-medium">${user.org_unit || '-'}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-diagram-3 text-primary me-2 mt-1"></i>
                                        <div>
                                            <small class="text-muted d-block">Unit Kerja</small>
                                            <span class="fw-medium">${user.work_unit || '-'}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-person-badge text-primary me-2 mt-1"></i>
                                        <div>
                                            <small class="text-muted d-block">Jabatan</small>
                                            <span class="fw-medium">${user.position || '-'}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `);

                        // Update hidden form
                        $('#hiddenForm').html(`
                            @csrf
                            <input type="hidden" name="phone" value="${phone}">
                            <input type="hidden" name="email" value="${user.email || ''}">
                            <input type="hidden" name="name" value="${user.name || ''}">
                            <input type="hidden" name="ktp" value="${user.ktp || ''}">
                            <input type="hidden" name="npwp" value="${user.npwp || ''}">
                            <input type="hidden" name="city" value="${user.city || ''}">
                            <input type="hidden" name="province" value="${user.province || ''}">
                            <input type="hidden" name="org_unit" value="${user.org_unit || ''}">
                            <input type="hidden" name="work_unit" value="${user.work_unit || ''}">
                            <input type="hidden" name="position" value="${user.position || ''}">
                        `);

                        // Tambahkan delay dan pastikan loading modal sudah tertutup
                        setTimeout(() => {
                            if (!loadingModal.classList.contains('show')) {
                                debugLog('Showing phone modal');
                                bsPhoneModal.show();
                            } else {
                                debugLog('Loading modal still shown, forcing cleanup');
                                bsLoadingModal.hide();
                                cleanupModals();
                                setTimeout(() => {
                                    bsPhoneModal.show();
                                }, 100);
                            }
                        }, 500);
                    } else {
                        debugLog('No data found or error in response');
                        alert(response.message || 'Data tidak ditemukan');
                    }
                } catch (error) {
                    console.error('Error in success callback:', error);
                    bsLoadingModal.hide();
                    cleanupModals();
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {
                    status: status,
                    error: error,
                    response: xhr.responseText,
                    state: xhr.state()
                });
                bsLoadingModal.hide();
                cleanupModals();
                alert('Terjadi kesalahan saat mencari data');
            },
            complete: function() {
                debugLog('AJAX request completed');
            }
        });
    });

    // Event listeners untuk modal
    loadingModal.addEventListener('show.bs.modal', function () {
        debugLog('Loading modal show event triggered');
    });

    loadingModal.addEventListener('shown.bs.modal', function () {
        debugLog('Loading modal shown event triggered');
    });

    loadingModal.addEventListener('hide.bs.modal', function () {
        debugLog('Loading modal hide event triggered');
    });

    loadingModal.addEventListener('hidden.bs.modal', function () {
        debugLog('Loading modal hidden event triggered');
        cleanupModals();
    });

    // Handle tombol close dengan try-catch
    document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(button => {
        button.addEventListener('click', function() {
            debugLog('Close button clicked');
            try {
                bsLoadingModal.hide();
                bsPhoneModal.hide();
                cleanupModals();
            } catch (error) {
                console.error('Error closing modals:', error);
            }
        });
    });

    // Handle tombol submit dengan error handling
    $('#btnSubmit').on('click', function() {
        debugLog('Submit button clicked');
        try {
            bsLoadingModal.show();
        } catch (error) {
            console.error('Error showing loading modal on submit:', error);
        }
        
        $.ajax({
            url: "{{ route('phone.submit') }}",
            method: "POST",
            data: $('#hiddenForm').serialize(),
            success: function(response) {
                debugLog('Submit success response:', response);
                bsLoadingModal.hide();
                cleanupModals();
                
                if (response.status === 'success') {
                    alert(response.message || 'Data berhasil diproses');
                    bsPhoneModal.hide();
                    $('#phoneForm')[0].reset();
                } else {
                    alert(response.message || 'Gagal memproses data');
                }
            },
            error: function(xhr, status, error) {
                console.error('Submit Error:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
                bsLoadingModal.hide();
                cleanupModals();
                alert('Terjadi kesalahan saat mengirim data');
            }
        });
    });

    // Tambahkan fungsi force cleanup
    function forceCleanupModals() {
        debugLog('Force cleaning up modals');
        try {
            // Hapus semua modal yang mungkin masih terbuka
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                }
            });
            
            // Bersihkan semua efek modal
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
            
            debugLog('Force modal cleanup completed');
        } catch (error) {
            console.error('Error during force modal cleanup:', error);
        }
    }

    // Tambahkan escape key handler
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            debugLog('Escape key pressed');
            forceCleanupModals();
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>