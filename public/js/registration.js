document.addEventListener('DOMContentLoaded', function() {
    // Sembunyikan container form saat awal
    const container = document.querySelector('.container');
    if (container) {
        container.style.display = 'none';
    }
    
    // Inisialisasi semua modal
    const termsModal = new bootstrap.Modal(document.getElementById('termsModal'), {
        backdrop: 'static',
        keyboard: false
    });
    
    const privacyPolicyModal = new bootstrap.Modal(document.getElementById('privacyPolicyModal'), {
        backdrop: 'static',
        keyboard: false
    });
    
    const customerAgreementModal = new bootstrap.Modal(document.getElementById('customerAgreementModal'), {
        backdrop: 'static',
        keyboard: false
    });

    // Inisialisasi checkbox dan button elements
    const privacyCheck = document.getElementById('privacyCheck');
    const agreementCheck = document.getElementById('agreementCheck');
    const agreeButton = document.getElementById('agreeButton');

    // Reset form visibility dan checkbox state
    if (container) container.style.display = 'none';
    if (privacyCheck) privacyCheck.checked = false;
    if (agreementCheck) agreementCheck.checked = false;

    // Event handlers untuk checkbox
    if (privacyCheck) {
        privacyCheck.addEventListener('click', function(e) {
            e.preventDefault();
            privacyPolicyModal.show();
        });
    }
    
    if (agreementCheck) {
        agreementCheck.addEventListener('click', function(e) {
            e.preventDefault();
            customerAgreementModal.show();
        });
    }

    // Fungsi untuk mengaktifkan tombol setuju di modal Privacy Policy
    const privacyPolicyModalElement = document.getElementById('privacyPolicyModal');
    if (privacyPolicyModalElement) {
        const modalBody = privacyPolicyModalElement.querySelector('.modal-body');
        const agreePrivacyButton = privacyPolicyModalElement.querySelector('#agreePrivacyPolicy');
        
        if (modalBody && agreePrivacyButton) {
            agreePrivacyButton.disabled = true;
            
            modalBody.addEventListener('scroll', function() {
                const isAtBottom = 
                    Math.abs(modalBody.scrollHeight - modalBody.scrollTop - modalBody.clientHeight) < 1;
                if (isAtBottom) {
                    agreePrivacyButton.disabled = false;
                }
            });
        }
    }

    // Fungsi untuk mengaktifkan tombol setuju di modal Customer Agreement
    const customerAgreementModalElement = document.getElementById('customerAgreementModal');
    if (customerAgreementModalElement) {
        const modalBody = customerAgreementModalElement.querySelector('.modal-body');
        const agreeCustomerButton = customerAgreementModalElement.querySelector('#agreeCustomerAgreement');
        
        if (modalBody && agreeCustomerButton) {
            agreeCustomerButton.disabled = true;
            
            modalBody.addEventListener('scroll', function() {
                const isAtBottom = 
                    Math.abs(modalBody.scrollHeight - modalBody.scrollTop - modalBody.clientHeight) < 1;
                if (isAtBottom) {
                    agreeCustomerButton.disabled = false;
                }
            });
        }
    }

    // Event handlers untuk tombol setuju di modal
    const agreePrivacyButton = document.getElementById('agreePrivacyPolicy');
    if (agreePrivacyButton) {
        agreePrivacyButton.addEventListener('click', function() {
            if (privacyCheck) privacyCheck.checked = true;
            privacyPolicyModal.hide();
            checkAgreements();
        });
    }

    const agreeCustomerButton = document.getElementById('agreeCustomerAgreement');
    if (agreeCustomerButton) {
        agreeCustomerButton.addEventListener('click', function() {
            if (agreementCheck) agreementCheck.checked = true;
            customerAgreementModal.hide();
            checkAgreements();
        });
    }

    // Fungsi untuk mengecek status checkbox
    function checkAgreements() {
        if (privacyCheck && agreementCheck && agreeButton) {
            agreeButton.disabled = !(privacyCheck.checked && agreementCheck.checked);
        }
    }

    // Event handler untuk tombol setuju utama
    if (agreeButton) {
        agreeButton.addEventListener('click', function() {
            sessionStorage.setItem('hasAgreedToTerms', 'true');
            termsModal.hide();
            if (container) {
                container.style.display = 'block';
            }
        });
    }

    // Reset status tombol saat modal ditutup
    if (privacyPolicyModalElement) {
        privacyPolicyModalElement.addEventListener('hidden.bs.modal', function() {
            const agreePrivacyButton = this.querySelector('#agreePrivacyPolicy');
            if (agreePrivacyButton) {
                agreePrivacyButton.disabled = true;
            }
        });
    }

    if (customerAgreementModalElement) {
        customerAgreementModalElement.addEventListener('hidden.bs.modal', function() {
            const agreeCustomerButton = this.querySelector('#agreeCustomerAgreement');
            if (agreeCustomerButton) {
                agreeCustomerButton.disabled = true;
            }
        });
    }

    // Tampilkan terms modal saat halaman dimuat
    setTimeout(() => {
        termsModal.show();
    }, 100);

    // Inisialisasi status checkbox
    checkAgreements();

    // Tangkap form submit dan tampilkan modal OTP
    const form = document.querySelector('form');
    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
    const otpModal = new bootstrap.Modal(document.getElementById('otpModal'));
    let formData = null;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            const phoneNumber = document.getElementById('phone').value;
            console.log('Attempting to send OTP to:', phoneNumber);
            
            const requestBody = { phone_number: phoneNumber };
            console.log('Request body:', requestBody);
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            console.log('CSRF Token:', csrfToken ? 'Present' : 'Missing');

            const response = await fetch('/verify/send-otp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(requestBody)
            });
            
            console.log('Response status:', response.status);
            console.log('Response headers:', {
                contentType: response.headers.get('content-type'),
                csrf: response.headers.get('x-csrf-token')
            });

            // Get raw response text first
            const responseText = await response.text();
            console.log('Raw response:', responseText);

            // Try to parse JSON
            let data;
            try {
                data = JSON.parse(responseText);
                console.log('Parsed response data:', data);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                throw new Error('Invalid JSON response: ' + responseText.substring(0, 100));
            }

            if (!response.ok) {
                console.error('Response not OK:', data);
                throw new Error(data.error || 'Terjadi kesalahan saat mengirim OTP');
            }
            
            console.log('OTP sent successfully:', data);
            otpModal.show();
            startOtpTimer();
            startResendTimer();
            
        } catch (error) {
            console.error('Full error details:', {
                message: error.message,
                name: error.name,
                stack: error.stack,
                response: error.response
            });
            
            // Show more detailed error message
            alert('Gagal mengirim OTP: ' + (error.message || 'Unknown error'));
        }
    });

    // Fungsi timer OTP
    function startOtpTimer() {
        let timeLeft = 300; // 5 menit
        const timerDisplay = document.getElementById('otpTimer');
        
        const timer = setInterval(() => {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            
            timerDisplay.textContent = 
                `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                clearInterval(timer);
                alert('Kode OTP telah kadaluarsa. Silakan coba lagi.');
                otpModal.hide();
            }
            
            timeLeft--;
        }, 1000);
    }

    // Fungsi timer untuk tombol kirim ulang
    function startResendTimer() {
        let timeLeft = 60;
        const resendBtn = document.getElementById('resendOtpBtn');
        const timerDisplay = document.getElementById('resendTimer');
        
        resendBtn.disabled = true;
        
        const timer = setInterval(() => {
            timerDisplay.textContent = timeLeft;
            
            if (timeLeft <= 0) {
                clearInterval(timer);
                resendBtn.disabled = false;
                timerDisplay.textContent = '60';
            }
            
            timeLeft--;
        }, 1000);
    }

    // Event listener untuk verifikasi OTP
    document.getElementById('verifyOtpBtn').addEventListener('click', async function() {
        const otpInput = document.getElementById('otpInput');
        const phoneNumber = document.getElementById('phone').value;
        
        try {
            const response = await fetch('/verify/verify-otp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    phone_number: phoneNumber,
                    otp: otpInput.value
                })
            });

            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || 'Verifikasi gagal');
            }

            alert('Verifikasi berhasil!');
            otpModal.hide();
            // Lanjutkan ke langkah berikutnya
            
        } catch (error) {
            console.error('Error:', error);
            alert('Gagal memverifikasi OTP: ' + error.message);
        }
    });

    // Event listener untuk kirim ulang OTP
    document.getElementById('resendOtpBtn').addEventListener('click', async function() {
        const phoneNumber = document.getElementById('phone').value;
        
        try {
            const response = await fetch('/verify/send-otp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ phone_number: phoneNumber })
            });

            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || 'Gagal mengirim ulang OTP');
            }

            startResendTimer();
            alert('Kode OTP baru telah dikirim');
            
        } catch (error) {
            console.error('Error:', error);
            alert('Gagal mengirim ulang OTP: ' + error.message);
        }
    });

    // Fungsi preview untuk KTP
    function previewImage(input, previewId, containerId) {
        const preview = document.getElementById(previewId);
        const container = document.getElementById(containerId);
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                container.style.display = 'block';
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Event listener untuk input KTP
    const ktpInput = document.getElementById('ktpPhoto');
    if (ktpInput) {
        ktpInput.addEventListener('change', function() {
            previewImage(this, 'ktpPreview', 'previewContainer');
        });
    }

    // Event listener untuk input selfie
    const selfieInput = document.getElementById('selfiePhoto');
    if (selfieInput) {
        selfieInput.addEventListener('change', function() {
            previewImage(this, 'selfiePreview', 'selfiePreviewContainer');
        });
    }
}); 