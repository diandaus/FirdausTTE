document.addEventListener('DOMContentLoaded', function() {
    const videoElement = document.getElementById('videoElement');
    const captureButton = document.getElementById('captureButton');
    const instructions = document.getElementById('instructions');
    let mediaRecorder;
    let recordedChunks = [];
    let isRecording = false;

    // Array instruksi gerakan
    const movements = [
        { text: 'Tunjukkan wajah Anda menghadap ke depan', duration: 3 },
        { text: 'Tolehkan wajah ke kanan', duration: 3 },
        { text: 'Tolehkan wajah ke kiri', duration: 3 },
        { text: 'Tunjukkan wajah Anda menghadap ke depan dan tersenyum', duration: 3 }
    ];

    let currentMovement = 0;

    async function startCamera() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ 
                video: { 
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    facingMode: "user"
                }, 
                audio: false 
            });
            videoElement.srcObject = stream;

            // Setup MediaRecorder
            mediaRecorder = new MediaRecorder(stream, {
                mimeType: 'video/webm;codecs=vp9'
            });

            mediaRecorder.ondataavailable = handleDataAvailable;
            mediaRecorder.onstop = handleStop;

            // Tampilkan SweetAlert dengan instruksi awal
            await Swal.fire({
                title: 'Verifikasi Wajah',
                html: `
                    <div class="text-start">
                        <p>Pastikan:</p>
                        <ul>
                            <li>Wajah terlihat jelas</li>
                            <li>Pencahayaan cukup</li>
                            <li>Tidak menggunakan masker</li>
                            <li>Tidak ada yang menghalangi wajah</li>
                        </ul>
                    </div>
                `,
                icon: 'info',
                confirmButtonText: 'Mulai Verifikasi',
                confirmButtonColor: '#0d6efd'
            });

            startRecording();
        } catch (err) {
            console.error('Error:', err);
            Swal.fire({
                title: 'Error',
                text: 'Tidak dapat mengakses kamera. Pastikan kamera terhubung dan izin diberikan.',
                icon: 'error'
            });
        }
    }

    function startRecording() {
        recordedChunks = [];
        isRecording = true;
        mediaRecorder.start();
        showMovementInstructions();
    }

    function showMovementInstructions() {
        if (currentMovement < movements.length) {
            const movement = movements[currentMovement];
            
            // Update instruksi dengan animasi
            instructions.innerHTML = `
                <div class="alert alert-primary fade show" role="alert">
                    <h4 class="alert-heading mb-2">Instruksi ${currentMovement + 1}/${movements.length}</h4>
                    <p class="mb-0">${movement.text}</p>
                    <div class="progress mt-2" style="height: 5px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" 
                             style="width: 0%"></div>
                    </div>
                </div>
            `;

            // Animasi progress bar
            const progressBar = instructions.querySelector('.progress-bar');
            progressBar.style.transition = `width ${movement.duration}s linear`;
            setTimeout(() => progressBar.style.width = '100%', 100);

            // Tunggu durasi gerakan selesai
            setTimeout(() => {
                currentMovement++;
                if (currentMovement < movements.length) {
                    showMovementInstructions();
                } else {
                    finishRecording();
                }
            }, movement.duration * 1000);
        }
    }

    function finishRecording() {
        if (isRecording && mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop();
            isRecording = false;
        }
    }

    function handleDataAvailable(event) {
        if (event.data.size > 0) {
            recordedChunks.push(event.data);
        }
    }

    async function handleStop() {
        const blob = new Blob(recordedChunks, { type: 'video/webm' });
        
        // Tampilkan loading
        Swal.fire({
            title: 'Memproses Video',
            html: 'Mohon tunggu...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            // Kirim video ke server
            const formData = new FormData();
            formData.append('video', blob);

            const response = await fetch('/video-verification/verify', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();

            if (result.success) {
                await Swal.fire({
                    title: 'Verifikasi Berhasil!',
                    text: 'Wajah Anda telah terverifikasi',
                    icon: 'success',
                    confirmButtonText: 'Lanjutkan',
                    confirmButtonColor: '#28a745'
                });
                
                // Redirect ke halaman berikutnya
                window.location.href = '/specimen';
            } else {
                throw new Error(result.message || 'Verifikasi gagal');
            }
        } catch (error) {
            console.error('Error:', error);
            await Swal.fire({
                title: 'Verifikasi Gagal',
                text: error.message || 'Terjadi kesalahan saat memproses video',
                icon: 'error',
                confirmButtonText: 'Coba Lagi',
                showCancelButton: true,
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Reset dan mulai ulang
                    currentMovement = 0;
                    startRecording();
                }
            });
        }
    }

    // Mulai kamera saat halaman dimuat
    startCamera();
}); 