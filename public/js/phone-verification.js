document.addEventListener('DOMContentLoaded', function() {
    const sendOTPBtn = document.getElementById('sendOTP');
    const verifyOTPBtn = document.getElementById('verifyOTP');
    const phoneInput = document.getElementById('phone');
    const otpInput = document.getElementById('otp');
    const otpVerification = document.getElementById('otpVerification');
    let timerInterval;

    function startTimer(duration) {
        let timer = duration;
        clearInterval(timerInterval);
        
        timerInterval = setInterval(function () {
            const minutes = parseInt(timer / 60, 10);
            const seconds = parseInt(timer % 60, 10);

            const display = document.getElementById('timer');
            display.textContent = minutes.toString().padStart(2, '0') + ':' + 
                                seconds.toString().padStart(2, '0');

            if (--timer < 0) {
                clearInterval(timerInterval);
                sendOTPBtn.disabled = false;
            }
        }, 1000);
    }

    sendOTPBtn.addEventListener('click', function() {
        const phone = phoneInput.value.trim();
        if (!phone) {
            alert('Masukkan nomor telepon');
            return;
        }

        fetch('/verify/send-otp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ phone_number: phone })
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                otpVerification.style.display = 'block';
                sendOTPBtn.disabled = true;
                startTimer(300); // 5 menit
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal mengirim OTP');
        });
    });

    verifyOTPBtn.addEventListener('click', function() {
        const otp = otpInput.value.trim();
        const phone = phoneInput.value.trim();

        if (!otp || otp.length !== 6) {
            alert('Masukkan 6 digit kode OTP');
            return;
        }

        fetch('/verify/verify-otp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                phone_number: phone,
                otp: otp
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                alert('Verifikasi berhasil');
                phoneInput.readOnly = true;
                otpVerification.style.display = 'none';
                clearInterval(timerInterval);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Verifikasi gagal');
        });
    });
}); 