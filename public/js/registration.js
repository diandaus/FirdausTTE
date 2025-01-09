// Sembunyikan form registrasi saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi form dan sembunyikan
    const registrationForm = document.querySelector('form');
    if (registrationForm) {
        registrationForm.style.display = 'none';
    }
    
    // Tampilkan modal syarat dan ketentuan saat halaman dimuat
    const termsModal = new bootstrap.Modal(document.getElementById('termsModal'));
    termsModal.show();

    // Fungsi untuk mengecek scroll di modal
    function handleModalScroll(modalId, buttonId) {
        const modalBody = document.querySelector(`#${modalId} .modal-body`);
        const agreeButton = document.querySelector(`#${buttonId}`);
        
        if (!modalBody || !agreeButton) {
            console.log('Modal elements not found:', modalId, buttonId);
            return;
        }

        // Reset button state dan scroll position
        agreeButton.disabled = true;
        modalBody.scrollTop = 0;
        modalBody.classList.remove('scrolled');

        function scrollHandler() {
            const scrollPosition = modalBody.scrollTop + modalBody.offsetHeight;
            const scrollHeight = modalBody.scrollHeight;
            
            if (scrollHeight - scrollPosition <= 30) {
                agreeButton.disabled = false;
                modalBody.classList.add('scrolled');
                modalBody.removeEventListener('scroll', scrollHandler);
            }
        }

        modalBody.addEventListener('scroll', scrollHandler);
        
        // Cek initial scroll
        setTimeout(scrollHandler, 100);
    }

    // Event listeners untuk checkbox
    const privacyCheck = document.getElementById('privacyCheck');
    const agreementCheck = document.getElementById('agreementCheck');
    let privacyModalShown = false;
    let agreementModalShown = false;

    if (privacyCheck) {
        privacyCheck.addEventListener('click', function(e) {
            if (!privacyModalShown) {
                e.preventDefault(); // Prevent checkbox from being checked
                const privacyModal = new bootstrap.Modal(document.getElementById('privacyPolicyModal'));
                privacyModal.show();
                
                // Setup modal event handlers
                document.getElementById('privacyPolicyModal').addEventListener('shown.bs.modal', function() {
                    handleModalScroll('privacyPolicyModal', 'agreePrivacyPolicy');
                });
            }
        });
    }

    if (agreementCheck) {
        agreementCheck.addEventListener('click', function(e) {
            if (!agreementModalShown) {
                e.preventDefault(); // Prevent checkbox from being checked
                const agreementModal = new bootstrap.Modal(document.getElementById('customerAgreementModal'));
                agreementModal.show();
                
                // Setup modal event handlers
                document.getElementById('customerAgreementModal').addEventListener('shown.bs.modal', function() {
                    handleModalScroll('customerAgreementModal', 'agreeCustomerAgreement');
                });
            }
        });
    }

    // Event handlers untuk tombol setuju di modal
    document.getElementById('agreePrivacyPolicy')?.addEventListener('click', function() {
        privacyModalShown = true;
        const modal = bootstrap.Modal.getInstance(document.getElementById('privacyPolicyModal'));
        modal?.hide();
        privacyCheck.checked = true;
        updateMainAgreeButton();
    });

    document.getElementById('agreeCustomerAgreement')?.addEventListener('click', function() {
        agreementModalShown = true;
        const modal = bootstrap.Modal.getInstance(document.getElementById('customerAgreementModal'));
        modal?.hide();
        agreementCheck.checked = true;
        updateMainAgreeButton();
    });

    // Fungsi untuk update status tombol setuju utama
    function updateMainAgreeButton() {
        const agreeButton = document.getElementById('agreeButton');
        if (agreeButton) {
            agreeButton.disabled = !(privacyCheck.checked && agreementCheck.checked);
        }
    }

    // Event handler untuk tombol setuju utama
    document.getElementById('agreeButton')?.addEventListener('click', function() {
        const termsModal = bootstrap.Modal.getInstance(document.getElementById('termsModal'));
        termsModal?.hide();
        
        // Tampilkan form registrasi
        if (registrationForm) {
            registrationForm.style.display = 'block';
            registrationForm.style.opacity = '0';
            setTimeout(() => {
                registrationForm.style.transition = 'opacity 0.5s ease-in';
                registrationForm.style.opacity = '1';
            }, 100);
        }
    });

    // Event listener untuk tombol daftar
    const submitButton = document.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.addEventListener('click', async function(e) {
            e.preventDefault();
            console.log('Submit button clicked');

            if (!validateForm()) {
                return false;
            }

            // Tampilkan loading
            Swal.fire({
                title: 'Mohon Tunggu',
                html: 'Sedang memproses pendaftaran...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const form = document.querySelector('form');
                const formData = new FormData(form);
                const emailInput = document.getElementById('email');
                const emailValue = emailInput?.value || '';

                // Debug: Log form data
                for (let pair of formData.entries()) {
                    console.log(pair[0] + ': ' + pair[1]);
                }

                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    redirect: 'follow'
                });

                // Debug: Log response details
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);

                let result;
                const responseText = await response.text();
                console.log('Raw response:', responseText);

                try {
                    result = JSON.parse(responseText);
                } catch (jsonError) {
                    console.error('JSON Parse Error:', jsonError);
                    throw new Error('Response tidak valid: ' + responseText.substring(0, 100));
                }

                if (result.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Registrasi Berhasil!',
                        html: `
                            <div class="text-center">
                                <div class="mb-4">
                                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                                </div>
                                <p class="mb-4">Data Anda telah berhasil terdaftar di Peruri</p>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary btn-lg proceed-verification">
                                        <i class="bi bi-camera-video me-2"></i>
                                        Lanjutkan Verifikasi Wajah
                                    </button>
                                </div>
                            </div>
                        `,
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        customClass: {
                            popup: 'animated fadeInDown',
                            container: 'custom-swal-container'
                        },
                        didRender: () => {
                            // Tambahkan event listener untuk tombol verifikasi
                            document.querySelector('.proceed-verification').addEventListener('click', function() {
                                window.location.href = '/video-verification';
                            });
                        }
                    });

                    // Tambahkan animasi CSS
                    const style = document.createElement('style');
                    style.textContent = `
                        .custom-swal-container {
                            z-index: 1500;
                        }
                        .animated {
                            animation-duration: 0.5s;
                            animation-fill-mode: both;
                        }
                        @keyframes fadeInDown {
                            from {
                                opacity: 0;
                                transform: translate3d(0, -20%, 0);
                            }
                            to {
                                opacity: 1;
                                transform: translate3d(0, 0, 0);
                            }
                        }
                        .fadeInDown {
                            animation-name: fadeInDown;
                        }
                        .proceed-verification {
                            background-color: #0d6efd;
                            border: none;
                            padding: 15px 25px;
                            font-size: 1.1rem;
                            transition: all 0.3s ease;
                        }
                        .proceed-verification:hover {
                            background-color: #0b5ed7;
                            transform: translateY(-2px);
                            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
                        }
                        .bi-camera-video {
                            font-size: 1.2rem;
                            vertical-align: middle;
                        }
                    `;
                    document.head.appendChild(style);
                } else {
                    let errorMessage = result.message || 'Terjadi kesalahan saat memproses pendaftaran';
                    let errorTitle = 'Registrasi Gagal';
                    let errorIcon = 'error';
                    
                    if (result.message && (
                        result.message.includes('Email Already Registered') || 
                        result.message.toLowerCase().includes('email sudah terdaftar')
                    )) {
                        errorTitle = 'Email Sudah Terdaftar';
                        errorMessage = `Email <strong>${emailValue}</strong> telah terdaftar sebelumnya.<br>Silakan gunakan alamat email lain.`;
                        errorIcon = 'warning';
                        
                        emailInput?.classList.add('is-invalid');
                        emailInput?.focus();
                    }

                    await Swal.fire({
                        icon: errorIcon,
                        title: errorTitle,
                        html: errorMessage,
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#dc3545'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                
                // Tampilkan error yang lebih detail
                let errorMessage = 'Terjadi kesalahan saat menghubungi server.';
                let errorDetail = error.message || '';
                
                if (errorDetail.includes('<!DOCTYPE')) {
                    errorMessage = 'Server mengalami masalah internal.';
                    errorDetail = 'Mohon coba beberapa saat lagi.';
                }

                await Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    html: `
                        <div class="text-start">
                            <p>${errorMessage}</p>
                            <small class="text-muted">${errorDetail}</small>
                        </div>
                    `,
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#dc3545'
                });
            } finally {
                // Pastikan loading modal tertutup
                Swal.close();
            }
        });
    }

    // Event listener untuk email input
    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    }

    // Fungsi untuk validasi form
    function validateForm() {
        const form = document.querySelector('form');
        if (!form) {
            console.log('Form not found');
            return false;
        }

        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        let emptyFields = [];

        // Reset validasi sebelumnya
        requiredFields.forEach(field => {
            field.classList.remove('is-invalid');
            
            if (!field.value.trim()) {
                console.log('Empty field found:', field.name);
                isValid = false;
                field.classList.add('is-invalid');
                
                // Ambil label field
                const label = field.previousElementSibling;
                const fieldName = label ? label.textContent.replace('*', '').trim() : field.name;
                emptyFields.push(fieldName);
            }
        });

        if (!isValid) {
            // Buat list field yang kosong
            const emptyFieldsList = emptyFields.map(field => `<li>${field}</li>`).join('');
            
            // Tampilkan SweetAlert
            Swal.fire({
                icon: 'error',
                title: 'Data Belum Lengkap!',
                html: `
                    <div class="text-start">
                        <p>Mohon lengkapi data berikut:</p>
                        <ul class="text-start">
                            ${emptyFieldsList}
                        </ul>
                    </div>
                `,
                confirmButtonText: 'Periksa Kembali',
                confirmButtonColor: '#dc3545',
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Scroll ke field pertama yang invalid
                    const firstInvalidField = document.querySelector('.is-invalid');
                    if (firstInvalidField) {
                        firstInvalidField.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'center' 
                        });
                        firstInvalidField.focus();
                    }
                }
            });

            return false;
        }

        return true;
    }

    // Event listener untuk input fields
    document.querySelectorAll('form [required]').forEach(field => {
        field.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    });
});

// Tambahkan CSS yang lebih sederhana untuk indikator scroll
document.head.insertAdjacentHTML('beforeend', `
    <style>
        .modal-body {
            overflow-y: auto;
            max-height: 70vh; /* Batasi tinggi modal */
        }
    </style>
`); 