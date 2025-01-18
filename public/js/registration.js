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
            
            if (!validateForm()) {
                return false;
            }

            try {
                const form = document.querySelector('form');
                const formData = new FormData(form);
                const emailInput = document.getElementById('email');
                const emailValue = emailInput?.value || '';

                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                let result;
                const responseText = await response.text();
                
                try {
                    result = JSON.parse(responseText);
                } catch (jsonError) {
                    console.error('JSON Parse Error:', jsonError);
                    throw new Error('Response tidak valid');
                }

                if (result.message && (
                    result.message.includes('Email Already Registered') || 
                    result.message.toLowerCase().includes('email sudah terdaftar')
                )) {
                    await Swal.fire({
                        icon: 'warning',
                        title: 'Email Sudah Terdaftar',
                        html: `
                            <div class="text-center">
                                <p class="mb-4">Email <strong>${emailValue}</strong> telah terdaftar sebelumnya.</p>
                                <p class="mb-4">Silakan lanjutkan proses verifikasi wajah atau gunakan email lain.</p>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary proceed-verification mb-2">
                                        <i class="bi bi-camera-video me-2"></i>
                                        Lanjutkan Verifikasi Wajah
                                    </button>
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">
                                        Gunakan Email Lain
                                    </button>
                                </div>
                            </div>
                        `,
                        showConfirmButton: false,
                        showCloseButton: true,
                        customClass: {
                            popup: 'animated fadeInDown'
                        },
                        didRender: () => {
                            document.querySelector('.proceed-verification').addEventListener('click', function() {
                                // Simpan email ke localStorage sebelum redirect
                                localStorage.setItem('registeredEmail', emailValue);
                                window.location.href = '/video-verification';
                            });
                        }
                    });

                    emailInput?.classList.add('is-invalid');
                    emailInput?.focus();
                } else if (!result.success) {
                    await Swal.fire({
                        icon: 'error',
                        title: 'Registrasi Gagal',
                        text: result.message || 'Terjadi kesalahan saat memproses pendaftaran',
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#dc3545'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                await Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: 'Mohon coba beberapa saat lagi',
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#dc3545'
                });
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

    // Helper functions
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function isValidKTP(ktp) {
        return /^\d{16}$/.test(ktp);
    }

    // Fungsi untuk validasi form
    function validateForm() {
        // Hanya field yang wajib diisi
        const requiredFields = [
            'name',
            'phone',
            'email',
            'ktp',
            'ktpPhoto',
            'address',
            'city',
            'province',
            'gender',
            'placeOfBirth',
            'dateOfBirth'
        ];

        let isValid = true;
        let emptyFields = [];

        // Reset validasi sebelumnya
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

        // Validasi hanya untuk field wajib
        requiredFields.forEach(field => {
            const element = document.getElementById(field);
            if (!element) return;

            let value;
            if (element.type === 'file') {
                value = element.files[0]; // File input
            } else {
                value = element.value.trim(); // Text input
            }

            let isEmpty = element.type === 'file' ? !value : !value || value === '';
            
            if (isEmpty) {
                element.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = 'Field ini wajib diisi';
                element.parentNode.appendChild(feedback);
                emptyFields.push(field);
                isValid = false;
            }
        });

        // Log hanya field wajib yang kosong
        if (emptyFields.length > 0) {
            console.log('Empty required fields:', emptyFields);
        }

        // Validasi format email jika diisi
        const email = document.getElementById('email')?.value;
        if (email && !isValidEmail(email.trim())) {
            const emailElement = document.getElementById('email');
            emailElement.classList.add('is-invalid');
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = 'Format email tidak valid';
            emailElement.parentNode.appendChild(feedback);
            isValid = false;
        }

        // Validasi format KTP jika diisi
        const ktp = document.getElementById('ktp')?.value;
        if (ktp && !isValidKTP(ktp.trim())) {
            const ktpElement = document.getElementById('ktp');
            ktpElement.classList.add('is-invalid');
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = 'NIK harus 16 digit angka';
            ktpElement.parentNode.appendChild(feedback);
            isValid = false;
        }

        return isValid;
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