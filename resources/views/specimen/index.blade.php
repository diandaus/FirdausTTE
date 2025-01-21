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
                    
                    <div class="divider"></div>
                    
                    <img src="{{ asset('images/logo.png') }}" 
                         alt="Logo Peruri" 
                         class="navbar-logo"
                         style="height: 35px;">
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">{{ __('Atur Spesimen Tanda Tangan') }}</h4>
                </div>

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

                    <form method="POST" action="{{ route('specimen.send') }}" enctype="multipart/form-data" id="specimenForm">
                        @csrf

                        <div class="mb-3" id="canvasSection">
                            <label class="form-label d-block">{{ __('Buat Tanda Tangan') }}</label>
                            <div class="border rounded p-3 bg-light">
                                <canvas id="signatureCanvas" 
                                        class="border rounded w-100" 
                                        style="height: 200px; cursor: crosshair;">
                                </canvas>
                                <div class="mt-2 d-flex gap-2">
                                    <button type="button" class="btn btn-secondary btn-sm" id="clearCanvas">
                                        Hapus
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm" id="saveSignature">
                                        Simpan Tanda Tangan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3" id="fileInputSection">
                            <label for="specimen" class="form-label">{{ __('Atau Upload Specimen Tanda Tangan Anda') }}*</label>
                            <input type="file" 
                                   class="form-control @error('specimen') is-invalid @enderror" 
                                   id="specimen" 
                                   name="specimen" 
                                   accept="image/*">

                            <input type="hidden" name="signature_data" id="signatureData">

                            @error('specimen')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Preview container -->
                        <div class="mt-3 mb-3" id="previewContainer" style="display: none;">
                            <label class="form-label">Preview Tanda Tangan</label>
                            <div class="border rounded p-3 bg-light">
                                <img id="specimenPreview" 
                                     src="" 
                                     alt="Preview" 
                                     class="img-thumbnail"
                                     style="max-height: 200px;">
                                <div class="mt-2">
                                    <button type="button" class="btn btn-secondary btn-sm" id="resetSignature">
                                        Buat Ulang
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary w-100">
                                {{ __('Kirim Specimen Tanda Tangan') }}
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
                <h4 class="mb-3">Terima Kasih!</h4>
                <p class="text-muted mb-4">Specimen tanda tangan Anda telah berhasil dikirim. Status penerbitan Sertifikat Anda akan segera diproses.</p>
                <a href="{{ route('phone.index') }}" class="btn btn-primary btn-lg w-100">
                    Selesai
                </a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
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
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
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
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    const canvas = document.getElementById('signatureCanvas');
    const ctx = canvas.getContext('2d');
    let isDrawing = false;
    let lastX = 0;
    let lastY = 0;

    // Set canvas size
    function resizeCanvas() {
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = rect.height;
        
        // Set drawing style
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
    }

    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    // Drawing functions
    function startDrawing(e) {
        isDrawing = true;
        [lastX, lastY] = [
            e.type === 'mousedown' ? e.offsetX : e.touches[0].clientX - canvas.getBoundingClientRect().left,
            e.type === 'mousedown' ? e.offsetY : e.touches[0].clientY - canvas.getBoundingClientRect().top
        ];
    }

    function draw(e) {
        if (!isDrawing) return;
        e.preventDefault();

        const currentX = e.type === 'mousemove' ? e.offsetX : e.touches[0].clientX - canvas.getBoundingClientRect().left;
        const currentY = e.type === 'mousemove' ? e.offsetY : e.touches[0].clientY - canvas.getBoundingClientRect().top;

        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(currentX, currentY);
        ctx.stroke();

        [lastX, lastY] = [currentX, currentY];
    }

    function stopDrawing() {
        isDrawing = false;
    }

    // Event listeners for mouse
    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseout', stopDrawing);

    // Event listeners for touch
    canvas.addEventListener('touchstart', startDrawing);
    canvas.addEventListener('touchmove', draw);
    canvas.addEventListener('touchend', stopDrawing);

    // Clear canvas
    document.getElementById('clearCanvas').addEventListener('click', function() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    });

    // Save signature
    document.getElementById('saveSignature').addEventListener('click', function() {
        const canvas = document.getElementById('signatureCanvas');
        // Get base64 without MIME prefix
        const base64Data = canvas.toDataURL('image/png').split(',')[1];
        document.getElementById('signatureData').value = base64Data;
        
        // Show preview
        const preview = document.getElementById('specimenPreview');
        preview.src = 'data:image/png;base64,' + base64Data;
        document.getElementById('previewContainer').style.display = 'block';
        
        // Hide file input
        document.getElementById('fileInputSection').style.display = 'none';
    });

    // Handle file upload preview
    document.getElementById('specimen').addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const preview = document.getElementById('specimenPreview');
                const previewContainer = document.getElementById('previewContainer');
                preview.src = e.target.result;
                previewContainer.style.display = 'block';
                
                // Clear canvas if file is uploaded
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                document.getElementById('signatureData').value = '';
                
                // Hide canvas section
                document.getElementById('canvasSection').style.display = 'none';
            }
            
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Jika ada success message dari session, tampilkan modal sukses
    @if(session('success'))
        $('#successModal').modal('show');
    @endif

    // Handle form submission
    $('#specimenForm').on('submit', function(e) {
        e.preventDefault();
        
        // Tampilkan loading
        Swal.fire({
            title: 'Mohon Tunggu',
            text: 'Sedang memproses specimen...',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        // Get form data
        const formData = new FormData(this);

        // Send AJAX request
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.close();
                
                if (response.success) {
                    // Tampilkan modal sukses
                    $('#successModal').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: response.message || 'Terjadi kesalahan saat mengirim specimen',
                        confirmButtonColor: '#dc3545'
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                
                let errorMessage = 'Terjadi kesalahan saat mengirim specimen';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: errorMessage,
                    confirmButtonColor: '#dc3545'
                });
            }
        });
    });

    // Reset signature
    document.getElementById('resetSignature').addEventListener('click', function() {
        // Clear preview
        document.getElementById('previewContainer').style.display = 'none';
        document.getElementById('specimenPreview').src = '';
        
        // Show both input methods
        document.getElementById('canvasSection').style.display = 'block';
        document.getElementById('fileInputSection').style.display = 'block';
        
        // Clear values
        document.getElementById('signatureData').value = '';
        document.getElementById('specimen').value = '';
        
        // Clear canvas
        const canvas = document.getElementById('signatureCanvas');
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    });
});
</script>
@endpush 