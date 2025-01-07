<!DOCTYPE html>
<html>
<head>
    <title>Video Verifikasi (E-KYC)</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    
    <!-- Tambahkan Bootstrap CSS dan JS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('styles')

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            background-color: #f4f4f4;
        }

        .container {
            width: 100%;
            max-width: 500px;
            text-align: center;
            padding: 10px;
        }

        h1 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 20px;
        }

        .email-section {
            margin-bottom: 20px;
            width: 100%;
        }

        label {
            font-size: 1rem;
            color: #555;
            display: block;
            text-align: left;
            margin-bottom: 5px;
        }

        input {
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .camera-container {
            position: relative;
            width: 100%;
            margin: 20px 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        #video {
            width: 100%;
            height: auto;
            display: block;
            background-color: #000;
        }

        .instruction {
            font-size: 1.2rem;
            color: #007BFF;
            margin: 15px 0;
            font-weight: bold;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        #recordingStatus {
            color: #dc3545;
            font-weight: bold;
            margin: 10px 0;
        }

        .button-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
            margin-top: 15px;
        }

        button {
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            border: none;
            border-radius: 8px;
            background-color: #007BFF;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
        }

        button:active {
            transform: translateY(1px);
        }

        button.stop {
            background-color: #dc3545;
        }

        button.stop:hover {
            background-color: #c82333;
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
            body {
                padding: 10px;
            }

            h1 {
                font-size: 1.5rem;
            }

            .instruction {
                font-size: 1rem;
            }

            button {
                padding: 10px;
            }

            .logo-container {
                gap: 10px;
                min-height: 50px;
            }

            .logo {
                height: 50px;
            }

            .logo:first-child {
                height: 40px;
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

        .text-muted {
            color: #6c757d !important;
        }

        /* Styling untuk loading modal */
        .modal-content {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
            color: #0d6efd;
        }

        .progress {
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar {
            background-color: #0d6efd;
            transition: width 0.2s ease;
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

        /* Animasi loading text */
        @keyframes ellipsis {
            0% { content: ''; }
            25% { content: '.'; }
            50% { content: '..'; }
            75% { content: '...'; }
        }

        .loading-text::after {
            content: '';
            animation: ellipsis 1.5s infinite;
        }
    </style>
    <script>
        // Cek apakah di localhost atau jaringan lokal
        const isLocalNetwork = window.location.hostname === 'localhost' || 
                              window.location.hostname === '127.0.0.1' ||
                              window.location.hostname.match(/^192\.168\./);

        async function setupCamera() {
            try {
                // Tambahkan opsi khusus untuk jaringan lokal
                let constraints = {
                    video: {
                        width: { ideal: 640 },
                        height: { ideal: 480 },
                        facingMode: "user"
                    },
                    audio: false
                };

                // Jika di jaringan lokal, tambahkan opsi ini
                if (isLocalNetwork) {
                    constraints.video.optional = [
                        { facingMode: "user" },
                        { allowHTTP: true }  // Izinkan HTTP untuk development
                    ];
                }

                const stream = await navigator.mediaDevices.getUserMedia(constraints);
                const video = document.getElementById('video');
                video.srcObject = stream;
                
                // Tambahkan event listener untuk memastikan video sudah siap
                video.onloadedmetadata = () => {
                    video.play();
                };

                return stream;
            } catch (error) {
                console.error('Error:', error);
                if (error.name === 'NotAllowedError') {
                    alert('Izin kamera ditolak. Silakan izinkan akses kamera di pengaturan browser Anda.');
                } else if (error.name === 'NotFoundError') {
                    alert('Kamera tidak ditemukan.');
                } else {
                    alert('Error mengakses kamera: ' + error.message);
                }
                throw error;
            }
        }

        // Fungsi untuk mengecek dukungan browser
        function checkBrowserSupport() {
            // Cek apakah browser mendukung getUserMedia
            if (!navigator.mediaDevices && !navigator.getUserMedia && 
                !navigator.webkitGetUserMedia && !navigator.mozGetUserMedia) {
                alert('Browser Anda tidak mendukung akses kamera. Silakan gunakan browser terbaru seperti:\n\n' +
                      '- Google Chrome versi 47+\n' +
                      '- Firefox versi 44+\n' +
                      '- Safari versi 11+\n' +
                      '- Edge versi 12+\n\n' +
                      'Atau coba akses melalui smartphone Anda.');
                return false;
            }
            return true;
        }

        // Polyfill untuk browser lama
        if (navigator.mediaDevices === undefined) {
            navigator.mediaDevices = {};
        }

        // Polyfill untuk getUserMedia
        if (navigator.mediaDevices.getUserMedia === undefined) {
            navigator.mediaDevices.getUserMedia = function(constraints) {
                const getUserMedia = navigator.webkitGetUserMedia || 
                                   navigator.mozGetUserMedia ||
                                   navigator.msGetUserMedia;

                if (!getUserMedia) {
                    return Promise.reject(new Error('Browser Anda tidak mendukung akses kamera. Silakan gunakan browser terbaru.'));
                }

                return new Promise(function(resolve, reject) {
                    getUserMedia.call(navigator, constraints, resolve, reject);
                });
            }
        }

        let mediaRecorder;
        let recordedChunks = [];
        let instructions = [
            "Kedipkan mata Anda",
            "Kedipkan mata Anda sekali lagi",
            "Silakan buka mulut Anda",
            "Tutup mulut Anda kembali"
        ];
        let currentInstruction = 0;
        let instructionInterval;
        let isRecording = false;
        let recordingComplete = false;

        async function startCamera() {
            if (!checkBrowserSupport()) {
                return;
            }

            const video = document.getElementById('video');
            
            try {
                console.log('Memulai akses kamera...');
                
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: "user",
                        width: { ideal: 640 },
                        height: { ideal: 480 }
                    },
                    audio: false 
                });
                
                console.log('Akses kamera berhasil:', stream);
                
                // Untuk browser lama yang tidak mendukung srcObject
                try {
                    video.srcObject = stream;
                } catch (error) {
                    // Fallback untuk browser lama
                    video.src = window.URL.createObjectURL(stream);
                }
                
                video.onloadedmetadata = () => {
                    console.log('Video metadata loaded');
                    video.play()
                        .catch(e => console.error('Error playing video:', e));
                };
                
                video.onerror = (err) => {
                    console.error('Error pada video element:', err);
                };
                
            } catch (err) {
                console.error("Error mengakses kamera:", err);
                
                let errorMessage = "Tidak dapat mengakses kamera.\n\n";
                
                if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                    errorMessage += "Mohon berikan izin akses kamera pada browser Anda.";
                } else if (err.name === 'NotFoundError') {
                    errorMessage += "Tidak ada kamera yang terdeteksi.";
                } else if (err.name === 'NotReadableError') {
                    errorMessage += "Kamera sedang digunakan oleh aplikasi lain.";
                } else if (err.name === 'ConstraintNotSatisfiedError') {
                    errorMessage += "Kamera Anda tidak memenuhi persyaratan teknis yang diperlukan.";
                } else {
                    errorMessage += err.message;
                }
                
                alert(errorMessage);
            }
        }

        // Tunggu hingga DOM sepenuhnya dimuat
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Halaman dimuat, memulai kamera...');
            startCamera();
        });

        function handleAction() {
            const button = document.getElementById('actionButton');
            
            if (!isRecording && !recordingComplete) {
                // Mulai verifikasi
                startRecording();
                button.style.display = 'none'; // Sembunyikan tombol selama verifikasi
            } else if (recordingComplete) {
                // Kirim ke Peruri
                saveRecording();
            }
        }

        function startRecording() {
            isRecording = true;
            recordingComplete = false;
            recordedChunks = [];
            const stream = document.getElementById('video').srcObject;
            
            // Konfigurasi MediaRecorder dengan codec yang sesuai
            const options = {
                mimeType: 'video/webm;codecs=h264',
                videoBitsPerSecond: 2500000 // 2.5 Mbps untuk kualitas yang baik
            };

            try {
                mediaRecorder = new MediaRecorder(stream, options);
            } catch (e) {
                // Fallback jika H.264 tidak didukung
                console.warn('H.264 tidak didukung, menggunakan codec default WebM');
                mediaRecorder = new MediaRecorder(stream, { mimeType: 'video/webm' });
            }
            
            mediaRecorder.ondataavailable = function(event) {
                if (event.data.size > 0) {
                    recordedChunks.push(event.data);
                }
            };

            mediaRecorder.start();
            document.getElementById('recordingStatus').textContent = "Recording...";
            
            currentInstruction = 0;
            showInstruction();
            instructionInterval = setInterval(showNextInstruction, 3000);
            speakInstruction(instructions[0]);
        }

        function showInstruction() {
            document.getElementById('currentInstruction').textContent = instructions[currentInstruction];
            speakInstruction(instructions[currentInstruction]);
        }

        function showNextInstruction() {
            currentInstruction++;
            if (currentInstruction >= instructions.length) {
                clearInterval(instructionInterval);
                completeRecording();
                return;
            }
            showInstruction();
        }

        function speakInstruction(text) {
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'id-ID'; // Set bahasa ke Indonesia
            speechSynthesis.speak(utterance);
        }

        function completeRecording() {
            isRecording = false;
            recordingComplete = true;
            mediaRecorder.stop();
            
            const button = document.getElementById('actionButton');
            button.textContent = 'Kirim';
            button.style.display = 'block';
            button.className = 'stop'; // Menggunakan style tombol merah

            document.getElementById('recordingStatus').textContent = "Verifikasi Selesai";
            document.getElementById('currentInstruction').textContent = "Silakan klik tombol Kirim";
        }

        async function saveRecording() {
            const button = document.getElementById('actionButton');
            const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
            const progressBar = document.querySelector('.progress-bar');
            
            try {
                button.disabled = true;
                loadingModal.show();

                const blob = new Blob(recordedChunks, { 
                    type: 'video/webm;codecs=h264'
                });
                
                // Simulasi progress upload
                let progress = 0;
                const progressInterval = setInterval(() => {
                    progress += 5;
                    if (progress <= 90) {
                        progressBar.style.width = progress + '%';
                    }
                }, 200);

                const base64Video = await blobToBase64(blob);
                const cleanBase64 = base64Video.split(';base64,').pop();
                
                const response = await fetch("{{ route('video.verify') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        video: cleanBase64
                    })
                });

                const result = await response.json();

                // Set progress ke 100% setelah upload selesai
                clearInterval(progressInterval);
                progressBar.style.width = '100%';

                // Tunggu sebentar sebelum menampilkan modal sukses
                await new Promise(resolve => setTimeout(resolve, 500));
                
                loadingModal.hide();

                if (result.success) {
                    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    successModal.show();
                } else {
                    throw new Error(result.message || 'Gagal mengirim video verifikasi');
                }

            } catch (error) {
                console.error('Error:', error);
                loadingModal.hide();
                alert('Gagal mengirim video verifikasi: ' + error.message);
            } finally {
                button.disabled = false;
                button.textContent = 'Kirim';
            }
        }

        // Fungsi untuk mengkonversi blob ke base64
        function blobToBase64(blob) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onloadend = () => resolve(reader.result);
                reader.onerror = reject;
                reader.readAsDataURL(blob);
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center">
                <img src="{{ asset('images/logors.png') }}" 
                     alt="Logo Pertama" 
                     class="navbar-logo"
                     style="height: 45px;">
                
                <div class="divider"></div>
                
                <img src="{{ asset('images/logo.png') }}" 
                     alt="Logo Kedua" 
                     class="navbar-logo"
                     style="height: 35px; margin-top: -10px;">
            </div>
        </div>

        <!-- Alert Notifikasi -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-12">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Perhatian!</strong>
                    </div>
                    <hr>
                    <p class="mb-0">Kami melakukan perekaman terhadap wajah anda untuk verifikasi data biometrik. 
                    Mohon untuk tidak mengenakan aksesoris di wajah (misal: Kacamata).</p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <h1>Video Verifikasi (E-KYC)</h1>

        <div class="camera-container">
            <video id="video" autoplay playsinline></video>
            <canvas id="canvas" style="display: none;"></canvas>
        </div>

        <div class="instruction" id="currentInstruction"></div>
        <p id="recordingStatus"></p>

        <div class="button-container">
            <button id="actionButton" onclick="handleAction()">Mulai Verifikasi</button>
        </div>
    </div>

    <!-- Tambahkan modal di bagian bawah body sebelum scripts -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="mb-3">Verifikasi Berhasil!</h4>
                    <p class="text-muted mb-4">Video verifikasi Anda telah berhasil dikirim dan sedang menunggu verifikasi.</p>
                    <a href="{{ route('specimen.index') }}" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-pen me-2"></i>
                        Atur Spesimen Tanda Tangan
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tambahkan modal loading sebelum modal sukses -->
    <div class="modal fade" id="loadingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="loadingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5 class="mb-2">Mohon Tunggu</h5>
                    <p class="text-muted mb-0">Sedang mengirim video verifikasi...</p>
                    <div class="progress mt-3" style="height: 6px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
