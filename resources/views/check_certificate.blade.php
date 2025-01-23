<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Sertifikat</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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

        @media (max-width: 480px) {
            .navbar-logo {
                height: 35px;
            }
            
            .divider {
                margin: 0 0.5rem;
            }
        }

        .footer-links {
            margin-top: auto;
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }

        .footer-links a {
            transition: color 0.2s ease;
        }

        .footer-links a:hover {
            color: #0d6efd !important;
        }

        @media (max-width: 576px) {
            .footer-links {
                padding: 1rem 0;
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
                    <p class="mb-0">Masukkan nomor telepon untuk memverifikasi keaslian sertifikat Anda.</p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body p-4">
                        <h4 class="card-title mb-4">Verifikasi Sertifikat</h4>
                        <form method="POST" action="{{ route('certificate.check') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="phone" class="form-label">Nomor Telepon</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                    <input type="tel" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone') }}"
                                           placeholder="Contoh: 08123456789" 
                                           pattern="[0-9]{10,13}"
                                           title="Masukkan nomor telepon yang valid (10-13 digit)"
                                           required>
                                </div>
                                <div class="form-text">Format: 08xxxxxxxxxx (10-13 digit)</div>
                                @error('phone')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search me-2"></i>Cek Status Sertifikat
                            </button>
                        </form>

                        @if(session('result'))
                            <!-- Sembunyikan alert default jika perlu verifikasi -->
                            @if(!isset(session('result')['needs_verification']))
                                <div class="mt-4">
                                    <div class="alert {{ session('result')['valid'] ? 'alert-success' : 'alert-danger' }}">
                                        <h5 class="alert-heading">
                                            <i class="bi {{ session('result')['valid'] ? 'bi-check-circle' : 'bi-x-circle' }} me-2"></i>
                                            {{ session('result')['message'] }}
                                        </h5>
                                        @if(session('result')['valid'])
                                            <hr>
                                            <div class="mt-3">
                                                <p class="mb-2"><strong><i class="bi bi-person me-2"></i>Nama:</strong> {{ session('result')['name'] }}</p>
                                                <p class="mb-0"><strong><i class="bi bi-check2-square me-2"></i>Status:</strong> 
                                                    <span class="badge bg-success">Aktif</span>
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer-links text-center py-4 mt-auto">
        <div class="container">
            <small class="text-muted">
                <a href="#" class="text-decoration-none text-muted me-2">Syarat dan Ketentuan</a> | 
                <a href="#" class="text-decoration-none text-muted ms-2">Kebijakan Privasi</a>
                <div class="mt-2">
                    © {{ date('Y') }} RS Islam Ibnu Sina Sigli. All rights reserved
                </div>
            </small>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('result') && isset(session('result')['needs_verification']))
            Swal.fire({
                title: '<strong>Verifikasi Diperlukan</strong>',
                icon: 'warning',
                html: `
                    <div class="text-start">
                        <p><strong>Nama:</strong> {{ session('result')['name'] }}</p>
                        <p class="mb-3"><strong>Status:</strong> Belum Verifikasi</p>
                        <p>Anda belum melakukan verifikasi wajah. Silakan lakukan verifikasi untuk mengaktifkan sertifikat Anda.</p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-camera-video me-2"></i>Mulai Verifikasi Wajah',
                cancelButtonText: 'Tutup',
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('video-verification.start') }}';
                    
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    
                    const emailInput = document.createElement('input');
                    emailInput.type = 'hidden';
                    emailInput.name = 'email';
                    emailInput.value = '{{ session('result')['email'] ?? '' }}';
                    
                    form.appendChild(csrfToken);
                    form.appendChild(emailInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        @endif

        // Debug untuk melihat response
        console.log('Session Result:', @json(session('result')));
    });
    </script>
</body>
</html> 