@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Logo Header -->
            <div class="d-flex justify-content-start align-items-center py-3">
                <div class="d-flex align-items-center">
                    <img src="{{ asset('images/logors.png') }}" 
                         alt="Logo Rumah Sakit" 
                         class="navbar-logo"
                         style="height: 45px;">
                    
                    <div class="divider" style="height: 40px; width: 1px; background-color: #dee2e6; margin: 0 1rem;"></div>
                    
                    <img src="{{ asset('images/logo.png') }}" 
                         alt="Logo Peruri" 
                         class="navbar-logo"
                         style="height: 35px; margin-top: -10px;">
                </div>
            </div>

            <!-- Alert Notifikasi -->
            <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Perhatian!</strong>
                </div>
                <hr>
                <p class="mb-0">Pastikan email dan nomor handphone Anda Benar, karena tidak dapat dirubah setelah melakukan registrasi.</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <!-- Form Registrasi -->
            <div class="card">
                <div class="card-header">{{ __('Data Registrasi') }}</div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('registration.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Form group untuk upload KTP -->
                        <div class="form-group mb-3">
                            <label for="ktpPhoto" class="form-label">{{ __('Foto KTP') }}*</label>
                            <input type="file" 
                                   class="form-control @error('ktpPhoto') is-invalid @enderror" 
                                   id="ktpPhoto" 
                                   name="ktpPhoto" 
                                   accept="image/*"
                                   required>

                            <!-- Preview container dengan style display: none default -->
                            <div class="mt-2 preview-wrapper" id="previewContainer" style="display: none;">
                                <img id="ktpPreview" 
                                     src="" 
                                     alt="" 
                                     class="img-thumbnail preview-image">
                            </div>

                            <input type="hidden" id="ktpPhotoPath" name="ktpPhotoPath" value="{{ old('ktpPhotoPath') }}">

                            @error('ktpPhoto')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Personal Information Section -->
                        <div class="section mb-4">
                            <h5 class="text-primary mb-3">Informasi Pribadi</h5>
                            
                            <div class="form-group mb-3">
                                <label class="form-label" for="name">{{ __('Nama Lengkap') }}*</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="phone">{{ __('Nomor Telepon') }}*</label>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                               id="phone" name="phone" value="{{ old('phone') }}" required>
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="email">{{ __('Email') }}*</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               id="email" name="email" value="{{ old('email') }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label" for="password">{{ __('Password') }}*</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" required>
                                <div class="helper-text">Minimal 8 karakter</div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="gender">{{ __('Jenis Kelamin') }}*</label>
                                        <select class="form-control @error('gender') is-invalid @enderror" 
                                                id="gender" name="gender" required>
                                            <option value="">Pilih Jenis Kelamin</option>
                                            <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Laki-laki</option>
                                            <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Perempuan</option>
                                        </select>
                                        @error('gender')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="dateOfBirth">{{ __('Tanggal Lahir') }}*</label>
                                        <input type="date" class="form-control @error('dateOfBirth') is-invalid @enderror" 
                                               id="dateOfBirth" name="dateOfBirth" value="{{ old('dateOfBirth') }}" required>
                                        @error('dateOfBirth')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label" for="placeOfBirth">{{ __('Tempat Lahir') }}*</label>
                                <input type="text" class="form-control @error('placeOfBirth') is-invalid @enderror" 
                                       id="placeOfBirth" name="placeOfBirth" value="{{ old('placeOfBirth') }}" required>
                                @error('placeOfBirth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Document Section -->
                        <div class="section mb-4">
                            <h5 class="text-primary mb-3">Dokumen</h5>

                            <div class="form-group mb-3">
                                <label class="form-label" for="ktp">{{ __('Nomor KTP') }}*</label>
                                <input type="text" class="form-control @error('ktp') is-invalid @enderror" 
                                       id="ktp" name="ktp" value="{{ old('ktp') }}" required>
                                @error('ktp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label" for="npwp">{{ __('Nomor NPWP') }}*</label>
                                <input type="text" class="form-control @error('npwp') is-invalid @enderror" 
                                       id="npwp" name="npwp" value="{{ old('npwp') }}" required>
                                @error('npwp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label" for="npwpPhoto">{{ __('Foto NPWP') }}*</label>
                                <input type="file" class="form-control @error('npwpPhoto') is-invalid @enderror" 
                                       id="npwpPhoto" name="npwpPhoto" accept="image/*" required
                                       onchange="previewImage(this, 'npwpPreview')">
                                <img id="npwpPreview" class="img-preview mt-2" style="max-width: 200px; display: none;">
                                @error('npwpPhoto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label" for="selfPhoto">{{ __('Foto Diri') }}*</label>
                                <input type="file" class="form-control @error('selfPhoto') is-invalid @enderror" 
                                       id="selfPhoto" name="selfPhoto" accept="image/*" required
                                       onchange="previewImage(this, 'selfPreview')">
                                <img id="selfPreview" class="img-preview mt-2" style="max-width: 200px; display: none;">
                                @error('selfPhoto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Address Section -->
                        <div class="section mb-4">
                            <h5 class="text-primary mb-3">Alamat</h5>

                            <div class="form-group mb-3">
                                <label class="form-label" for="address">{{ __('Alamat Lengkap') }}*</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                          id="address" name="address" rows="3" required>{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="city">{{ __('Kota') }}*</label>
                                        <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                               id="city" name="city" value="{{ old('city', 'Sigli') }}" required>
                                        @error('city')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="province">{{ __('Provinsi') }}*</label>
                                        <input type="text" class="form-control @error('province') is-invalid @enderror" 
                                               id="province" name="province" value="{{ old('province', 'Aceh') }}" required>
                                        @error('province')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Organization Section -->
                        <div class="section mb-4">
                            <h5 class="text-primary mb-3">Informasi Organisasi</h5>

                            <div class="form-group mb-3">
                                <label class="form-label" for="orgUnit">{{ __('Unit Organisasi') }}</label>
                                <input type="text" class="form-control @error('orgUnit') is-invalid @enderror" 
                                       id="orgUnit" name="orgUnit" value="{{ old('orgUnit') }}">
                                @error('orgUnit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label" for="workUnit">{{ __('Unit Kerja') }}</label>
                                <input type="text" class="form-control @error('workUnit') is-invalid @enderror" 
                                       id="workUnit" name="workUnit" value="{{ old('workUnit') }}">
                                @error('workUnit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label" for="position">{{ __('Jabatan') }}</label>
                                <input type="text" class="form-control @error('position') is-invalid @enderror" 
                                       id="position" name="position" value="{{ old('position') }}">
                                @error('position')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary w-100">
                                {{ __('Daftar') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="mb-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                </div>
                <h4 class="mb-3">Registrasi Berhasil!</h4>
                <a href="{{ route('video-verification.index') }}" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-camera-video me-2"></i>
                    Lanjutkan Verifikasi Wajah
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="loadingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="loadingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="mb-2">Mohon Tunggu</h5>
                <p class="text-muted mb-0">Sedang memproses data registrasi Anda...</p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Inisialisasi preview saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    const ktpPhotoPath = document.getElementById('ktpPhotoPath').value;
    const previewContainer = document.getElementById('previewContainer');
    const preview = document.getElementById('ktpPreview');
    
    if (ktpPhotoPath) {
        preview.src = ktpPhotoPath;
        previewContainer.style.display = 'block';
    }
});

// Preview handler untuk file input
function handleImagePreview(input) {
    const previewContainer = document.getElementById('previewContainer');
    const preview = document.getElementById('ktpPreview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = '';
        previewContainer.style.display = 'none';
    }
}

// Event listener untuk KTP photo
document.getElementById('ktpPhoto').addEventListener('change', function() {
    handleImagePreview(this);
    handleKTPUpload(this);
});

// KTP upload dan OCR handler
async function handleKTPUpload(input) {
    if (!input.files || !input.files[0]) return;
    
    try {
        const formData = new FormData();
        formData.append('ktpPhoto', input.files[0]);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        const response = await fetch('{{ route("ocr.ktp") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            const result = data.result;
            
            fillFormField('name', result.name);
            fillFormField('ktp', result.nik);
            fillFormField('placeOfBirth', result.birthPlace);
            fillFormField('dateOfBirth', result.birthDate);
            fillFormField('gender', result.gender === 'LAKI-LAKI' ? 'M' : 
                                  result.gender === 'PEREMPUAN' ? 'F' : '');
            fillFormField('address', result.address);
            
            document.getElementById('ktpPhotoPath').value = result.imageUrl;
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Gagal membaca data KTP secara otomatis. Silakan isi form secara manual.');
    }
}

function fillFormField(id, value) {
    const field = document.getElementById(id);
    if (field && value) {
        field.value = value;
        field.classList.add('auto-filled');
        field.classList.add('is-valid');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Cek jika ada flash message success
    @if(session('success'))
        // Tampilkan modal
        var successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
    @endif
});

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            // Tampilkan loading modal
            loadingModal.show();
            
            // Disable submit button
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            
            // Submit form
            const formData = new FormData(this);
            const response = await fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            // Sembunyikan loading modal
            loadingModal.hide();
            
            if (result.success) {
                // Tampilkan success modal
                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
            } else {
                throw new Error(result.error || 'Terjadi kesalahan saat memproses data');
            }
            
        } catch (error) {
            // Sembunyikan loading modal
            loadingModal.hide();
            
            // Enable kembali submit button
            submitBtn.disabled = false;
            
            // Tampilkan pesan error
            alert(error.message || 'Terjadi kesalahan. Silakan coba lagi.');
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.preview-wrapper {
    min-height: 0;
    padding: 0;
    margin: 0;
}

.preview-wrapper:empty {
    display: none !important;
}

.preview-image {
    width: auto;
    height: auto;
    max-height: 150px;
    object-fit: contain;
    border: 1px solid #dee2e6;
    padding: 0.25rem;
    background-color: #fff;
    cursor: default;
}

.input-group {
    margin-bottom: 8px;
}

.auto-filled {
    background-color: #e8f0fe !important;
    transition: background-color 0.3s ease;
}

/* Tambahkan style untuk icon */
.alert i {
    font-size: 1.2rem;
}

.alert hr {
    margin: 0.5rem 0;
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
    display: inline-block;
    margin: 0 1rem;
}

@media (max-width: 480px) {
    .navbar-logo {
        height: 35px;
    }
    
    .divider {
        margin: 0 0.5rem;
    }
}

.modal-content {
    border-radius: 15px;
    border: none;
}

.modal-body {
    padding: 2rem;
}

.bi-check-circle-fill {
    color: #198754;
    filter: drop-shadow(0 0 10px rgba(25, 135, 84, 0.3));
}

.btn-lg {
    padding: 12px 24px;
    font-size: 1.1rem;
    border-radius: 8px;
}

/* Styling untuk loading modal */
.modal-content {
    border: none;
    border-radius: 15px;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
}

/* Animasi fade untuk modal */
.modal.fade .modal-dialog {
    transition: transform .3s ease-out;
    transform: scale(0.95);
}

.modal.show .modal-dialog {
    transform: scale(1);
}

/* Style untuk disabled button */
button:disabled {
    cursor: not-allowed;
    opacity: 0.7;
}
</style>
@endpush 