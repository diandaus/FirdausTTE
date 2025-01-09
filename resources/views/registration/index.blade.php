@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/registration.css') }}" rel="stylesheet">
@endpush

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

                            <div class="mt-2 preview-wrapper" id="previewContainer" style="display: none;">
                                <img id="ktpPreview" 
                                     src="" 
                                     alt="Preview KTP" 
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

<!-- Modal Syarat dan Ketentuan -->
<div class="modal fade" id="termsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header flex-column align-items-center pb-0">
                <!-- Logo -->
                <div class="modal-logo mb-3">
                    <img src="{{ asset('images/logo.png') }}" 
                         alt="Logo Peruri" 
                         style="height: 35px;">
                </div>
                <!-- Title -->
                <h5 class="modal-title text-center" id="termsModalLabel">
                    Syarat dan Ketentuan
                </h5>
            </div>
            <hr class="modal-divider">
            <div class="modal-body">
            <style>
.modal-header {
    padding-top: 2rem;
    border-bottom: none;
}

.modal-logo {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-title {
    font-weight: 600;
    font-size: 1.5rem;
    color: #333;
    margin-bottom: 1rem;
}

.modal-divider {
    width: 90%;
    height: 2px;
    background-color: #dee2e6;
    margin: 0 auto 1.5rem auto;
    opacity: 1;
}
</style>
                <!-- Kebijakan Privasi -->
                                   
                <div class="form-check">
    <input class="form-check-input" type="checkbox" id="privacyCheck">
    <label class="form-check-label" for="privacyCheck">
        Saya setuju dengan <span class="text-blue">Kebijakan Privasi</span>
    </label>
</div>

<div class="form-check">
    <input class="form-check-input" type="checkbox" id="agreementCheck">
    <label class="form-check-label" for="agreementCheck">
        Saya setuju dengan <span class="text-blue">Perjanjian Pelanggan</span>
    </label>
</div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="window.history.back()">Kembali</button>
                <button type="button" class="btn btn-primary" id="agreeButton" disabled>Setuju</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal Kebijakan Privasi -->
<div class="modal fade" id="privacyPolicyModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="privacyPolicyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header flex-column align-items-center pb-0">
                <!-- Logo -->
                <div class="modal-logo mb-3">
                    <img src="{{ asset('images/logo.png') }}" 
                         alt="Logo Peruri" 
                         style="height: 35px;">
                </div>
                <!-- Title -->
                <h5 class="modal-title text-center" id="privacyPolicyModalLabel">
                    Kebijakan Privasi
                </h5>
            </div>
            <hr class="modal-divider">
            <div class="modal-body" style="text-align: justify;">

                <!-- Paragraf Pembuka -->
                <div class="section mb-4">
                    <p>Peruri CA sangat menghormati privasi Anda. Kebijakan Privasi ini telah disusun oleh Peruri CA untuk disepakati bersama Anda tentang praktik privasi yang dilaksanakan oleh Peruri CA sehubungan dengan situs web, produk, dan layanannya.</p>
                    
                    <p>Kebijakan privasi ini mematuhi peraturan perundang-undangan terkait perlindungan Data Pribadi yang memberikan kesempatan kepada Anda untuk mengetahui, memahami, dan menyetujui bagaimana Peruri CA mengumpulkan, mengolah, menganalisa, menggunakan, menyimpan, menampilkan, mengumumkan, menyebarluaskan, menghapus, dan memusnahkan Data Pribadi Anda yang diberikan kepada Peruri CA pada saat Anda melakukan registrasi dan menggunakan layanan Peruri CA.</p>

                    <p>Dengan menggunakan layanan Peruri CA, maka Anda menyetujui praktik yang dijelaskan dalam Kebijakan Privasi ini.</p>
                </div>

                <!-- Daftar Istilah -->
                <div class="section">
                <h6 class="section-title"><span class="section-number">1.</span><span class="section-heading">Daftar Istilah</span></h6>
                    <div class="section-content">
                        <p class="section-intro">Dalam Perjanjian ini, yang dimaksud dengan:</p>
                        <ul class="custom-list">
                            <li>
                                <span class="list-marker">a.</span>
                                <span class="list-content"><strong class="term">Anda</strong> adalah pihak pengguna Sertifikat Elektronik atau pelanggan.</span>
                            </li>
                            <li>
                                <span class="list-marker">b.</span>
                                <span class="list-content"><strong class="term">Data Pribadi</strong> adalah data tentang orang perseorangan yang teridentifikasi atau dapat diidentifikasi secara tersendiri atau dikombinasi dengan informasi lainnya baik secara langsung maupun tidak langsung melalui sistem elektronik atau nonelektronik.</span>
                            </li>
                            <li>
                                <span class="list-marker">c.</span>
                                <span class="list-content"><strong class="term">Sertifikat Eletronik</strong> adalah sertifikat yang bersifat elektronik yang memuat tanda tangan elektronik dan identitas yang menunjukkan status subjek hukum para pihak dalam transaksi elektronik yang dikeluarkan oleh Peruri CA sebagai Penyelenggara Sertifikasi Elektronik (PSrE).</span>
                            </li>
                            <li>
                                <span class="list-marker">d.</span>
                                <span class="list-content"><strong class="term">Peruri CA</strong> adalah unit bisnis Peruri yang memberikan layanan tanda tangan elektronik, Sertifikat Elektronik, dan segel elektronik.</span>
                            </li>
                            <li>
                                <span class="list-marker">e.</span>
                                <span class="list-content"><strong class="term">Certification Practice Statement (CPS)</strong> adalah dokumen Peruri CA yang berisi kebijakan dan prosedur yang digunakan untuk mengoperasikan infrastruktur Kunci Publik. Dokumen CPS Peruri	CA	tersedia	di https://ca.peruri.co.id/ca/legal .</span>
                            </li>
                            <li>
                                <span class="list-marker">f.</span>
                                <span class="list-content"><strong class="term">Situs Repositori</strong> adalah situs dari Peruri CA yaitu https://ca.peruri.co.id/ca/legal.</span>
                            </li>                    
                        </ul>
                    </div>
                </div>

                <!-- Pengumpulan Data Pribadi -->
                <h6>2. Pengumpulan Data Pribadi</h6>
                <p>Peruri CA mengumpulkan Data Pribadi Anda ketika Anda:</p>
                <ul>
                    <li>a. Melakukan pemesanan atau pendaftaran untuk produk atau layanan Sertifikat Elektronik dari Peruri CA;</li>
                    <li>b. Melakukan pengkinian Data Pribadi;</li>
                    <li>c. Mengakses layanan Peruri CA;</li>
                    <li>d. Menanggapi survei; dan</li>
                    <li>e. Mengisi formulir untuk bantuan pra/pasca penjualan.</li>
                </ul>
                <p>Agar Peruri CA dapat memenuhi permintaan produk atau layanan Sertifikat Elektronik, Anda harus memberikan Data Pribadi yang benar, akurat, terkini, dan lengkap kepada Peruri CA, termasuk namun tidak terbatas pada:</p>
                <ul>                   
                <li>a. Informasi pribadi seperti nama, tempat dan tanggal lahir, Kartu Tanda Penduduk (KTP) atau kartu identitas lainnya (termasuk informasi yang ada di dalamnya), alamat email,alamat tempat tinggal,nomor telepon, dan data biometrik;</li>
                <li>b. Informasi hubungan yang membantu Peruri CA melakukan bisnis dengan Anda, seperti jenis produk dan layanan yang mungkin menarik bagi Anda, kontak dan preferensi produk, bahasa, preferensi pemasaran dan data demografis;</li>
                <li>c. Informasi transaksional tentang bagaimana Anda berinteraksi dengan Peruri CA, termasuk pembelian, pertanyaan, informasi akun pelanggan, detail organisasi, riwayat transaksi dan korespondensi, dan informasi tentang bagaimana Anda menggunakan dan berinteraksi dengan situs web Peruri CA;dan</li>
                <li>d. Pada saat Anda menggunakan layanan, Peruri CA akan mengumpulkan Data Pribadi secara otomatis seperti IP Address, device information, login information, browser client & version, operating system, dan data transaksi Pengguna yang berkaitan dengan penggunaan layanan Peruri CA.</li>
            </ul>
            <p>Dalam rangka memproses pesanan Sertifikat Elektronik dan meningkatkan layanan, Peruri CA dapat mengumpulkan informasi tambahan menggunakan data yang bersumber dari pihak ketiga yang telah bekerja sama dengan Peruri CA, antara lain lembaga pemerintahan seperti Dinas Kependudukan dan Catatan Sipil dan lembaga swasta.</p>
            <p>Peruri CA tidak mengumpulkan Data Pribadi calon pelanggan yang tidak menyelesaikan proses registrasi atau yang registrasinya ditolak.</p>                
            <p>Jika Anda memberikan informasi yang tidak benar, tidak akurat, tidak terkini, tidak lengkap, atau jika Peruri CA memiliki alasan yang kuat untuk mencurigai bahwa informasi tersebuttidak benar,tidak akurat,tidak terkini, atau tidak lengkap, Peruri CA memiliki hak untuk menangguhkan atau menghentikan akun Anda dan menolak segala dan semua layanan saat ini atau masa depan.</p>
            <p>PeruriCA berkomitmen untuk merahasiakan dan melindungi Data Pribadi Anda, kecuali untuk informasi yang tercantum dalam Sertifikat Elektronik yang diterbitkan.</p>
            <h6>3. Pengunaan Data Pri</h6><!-- Lanjutkan dengan semua bagian lainnya... -->
                <p>Data Pribadi Anda akan digunakan untuk tujuan yang di tentukan di bawah ini:</p>
                <ul>
                    <li>a. Memeriksa kebenaran identitas</li>
                    <p>Peruri CA menggunakan Data Pribadi Anda untuk melakukan pemeriksaan atas kebenaran identitas Anda.</p>
                    <li>b. Memproses permintaan untuk produk dan layanan Peruri CA.</li>
                    <p>Data Pribadi Anda digunakan untuk menyediakan produk dan layanan, pemrosesan pesanan, dan untuk melakukan transaksi bisnis seperti penagihan.</p>
                    <li>c. Meningkatkan layanan pelanggan</li>
                    <p>Data Pribadi Anda membantu Peruri CA untuk lebih efektif dalam memberikan dukungan teknis dan meningkatkan pelayanan.</p>
                    <li>d. Mengirim pemberitahuan pembaruan</li>
                    <p>Data Pribadi Anda dapat digunakan untuk mengirimkan pemberitahuan pembaruan untuk Sertifikat Elektronik Anda yang akan kedaluwarsa.</p>
                    <li>e. Mengirim informasi layanan terbaru</li>
                    <p>Peruri CA dapat mengirimkan informasi layanan terbaru, pembaharuan keamanan, informasi produk atau layanan terkait, dan pembaharuan status pada tampilan pemeliharaan atau ketersediaan layanan.</p>
                    <li>f. Memberi tahu Anda tentang produk dan layanan Peruri CA</li>
                    <p>Peruri CA dapat mengirimkan kepada Anda buletin perusahaan secara berkala, informasi tentang produk dan layanan yang Peruri CA anggap menarik bagi Anda berdasarkanpada penggunaan produk dan layanan Peruri CA lainnya, undangan untuk kehadiran Anda di acara pemasaran yang disponsori Peruri CA seperti webinar,dan/atau berkomunikasi dengan Anda sehubungan dengan layanan Peruri CA.</p>
                </ul>
                
                <h6>4. Pengungkapan dan Transfer Data Pribadi</h6>
                <p>Peruri CA tidak menjual Data Pribadi Anda kepada pihak lain.</p>
                <p>Dengan menyetujui Kebijakan Privasi ini, Anda memberikan persetujuan kepada Peruri CAuntuk dapat melakukan pengungkapan dan transfer Data Pribadi yang tercantum dalam Kebijakan Privasi ini.</p>
                <p>Peruri CA secara terbuka mengungkapkan Data Pribadi Anda, termasuk tetapi tidak terbatas pada Data Pribadi yang tercantum dalam Sertifikat Elektronik yang diterbitkan oleh Peruri CA atau Data Pribadi yang terdapat dalam akun Anda, yang dibutuhkan untuk memenuhi layanan yang Anda gunakan.</p>
                <p>Selain ketentuan pengungkapan tersebut, Peruri CA dapat membagikan Data Pribadi Anda pada:</p>
                <ul>
                    <li>a. pihak ketiga yang merupakan bagian dari organisasi Peruri, anak perusahaan Peruri, dan perusahaan afiliasi Peruri sehubungan dengan pengelolaan bisnis;</li>
                    <li>b. Pindividu, organisasi, entitas, otoritas pemerintahan, dan aparat penegak hukum dimana Peruri CA memiliki kewajiban terhadapnya untuk memenuhi ketentuan hukum	dan	peraturan perundang-undangan, dalam rangka proses penegakan hukum, pengambilan tindakan pencegahan sehubungan dengan kegiatan yang tidak sah, dugaan pelanggaran hukum, tindak pidana, atau pelanggaran peraturan perundang-undangan;</li>
                    <li>c. pihak ketiga sebagai auditor yang berwenang dimana pihak ketiga tersebut diharuskan untuk mematuhi persyaratan kerahasiaan agar tidak mengungkapkan, menjual,	memperdagangkan, mendistribusikan, dan/atau menggunakan Data Pribadi Anda;</li>
                    <li>d. agen, vendor, kontraktor, pemasok, atau pihak ketiga lainnya yang menyediakan layanan kepada Peruri CA sehingga Peruri CA dapat menyediakan layanan kepada Anda. Peruri CA memastikan bahwa agen, vendor, kontraktor, pemasok, atau pihak ketiga lainnya tersebut hanya akan menggunakan Data Pribadi Anda sesuai keperluan untuk mendukunglayananPeruri CA; dan</li>
                    <li>e. pihak ketiga yang melanjutkan kegiatan usaha Peruri CA apabila terjadi penggabungan, pemisahan, atau pengambilalihan kegiatan usaha Peruri CA</li>
                </ul>
                
                <h6> 5. Penyimpanan dan Retensi Data</h6>
                <p>Data Pribadi yang Peruri CA kumpulkan akan dipertahankan sesuai jangka waktu retensi yang tercantum di CPS Peruri CA atau untuk periode yang secara khusus diwajibkan oleh hukum atau peraturan yang wajib diikuti oleh Peruri CA.</p>
                <p>Untuk memenuhi persyaratan audit Root CA sebagaimana dirinci dalam CPS Peruri CA, Data Pribadi yang digunakan untuk memenuhi verifikasi jenis aplikasi Sertifikat Elektronik tertentu akan disimpan selama minimal 5 tahun tergantung pada kelas produk atau layanan dan dapat disimpan baik dalam format fisik atau elektronik. Setelah jangka waktu tersebut Peruri CA akan tetap menyimpan Data Pribadi sesuai dengan kesepakatan Anda kecuali Anda menghendaki secara tertulis untuk dilakukan penghapusan data. Silakan merujuk pada CPS Peruri CA untuk perincian lengkap.</p>
                <p>Setelah periode penyimpanan berakhir, Peruri CA memusnahkan Data Pribadi Anda untuk mencegah kehilangan, pencurian, penyalahgunaan, atau akses tidak sah.</p>
                
                <h6>6. Pengamanan Data Pribadi</h6>
                <p>Anda. Informasi yang Anda berikan ketika melakukan transaksi dengan memasukkan, mengunggah, mengirim, dan mengakses Data Pribadi Anda akan terkirim secara rahasia dan tersimpan dengan aman. Peruri CA menerapkan standar keamanan informasi yang tinggi dan sesuai dengan peraturan perundang-undangan untuk melindungi Data Pribadi Anda.</p>
                <p>Anda bertanggung jawab untuk menjaga kerahasiaan Data Pribadi Anda, kata sandi, One Time Password (OTP), dan token yang dihasilkan oleh aplikasi KEYLA serta menjaga keamanan perangkat yang Anda gunakan untuk mengakses layanan Peruri CA. Peruri CA tidak bertanggung jawab atas segala kerugian yang timbul akibat kelalaian Anda dalam menjaga kerahasiaan kata sandi, OTP, atau token yang dihasilkan oleh aplikasi KEYLA dan kelalaian Anda dalam menjaga kemanan perangkat.</p>
                
                <h6>7. Hak Anda</h6>
                <ul>
                    <li>a. Permintaan Akses</li>
                    <p>Anda memiliki hak untuk mengakses dan mengubah Data Pribadi Anda yang tersimpan di sistem Peruri CA.</p>
                    <li>b. Perubahan</li>
                    <p>Anda berhak memelihara, mengubah, dan memperbarui Data Pribadi Anda agar tetap benar, akurat, terkini, dan lengkap.</p>
                    <li>c. Penghapusan</li>
                    <p>Anda memiliki hak untuk meminta penghapusan Data Pribadi Anda dari sistem Peruri CA. Penghapusan Data Pribadi berdampak pada penutupan akun dan layanan Peruri CA bagi Anda. Penghapusan hanya dapat dilakukan terhadap Data Pribadi yang tersimpan di area produksi, dan akan dilakukan sesuai prosedur yang aman. Dalam hal Data Pribadi sudah diarsipkan, maka ketentuan ini tidak berlaku dan Data Pribadi akan terhapus sesuai masa retensinya.</p>
                    <li>d. Menarik Persetujuan</li>
                    <p>Jika Peruri CA sedang memproses Data Pribadi Anda berdasarkan persetujuan Anda, Anda dapat menarik persetujuan pemrosesan Data Pribadi Anda kapan saja. Penarikan persetujuan Anda berdampak pada penutupan akun dan layanan PeruriCA bagi Anda.</p>
                </ul>
                <p>Anda dapat menggunakan hak Anda dengan menghubungi Peruri CA secara tertulis, melalui e-mail ke alamat cs.digital@peruri.co.id.</p>
                <p>Peruri CA akan meminta dan Anda wajib untuk memberikan identifikasi dalam rangka memverifikasi keakuratan data dan keaslian sebagai subjek data. Peruri CA akan melakukan upaya yang wajar untuk menanggapi dan memproses permintaan Anda sebagaimana diharuskan oleh hukum.</p>

                <h6>8. Tempat Pemrosesan dan Penyimpanan</h6>
                <p>Data Pribadi ditempatkan dan diproses di wilayah hukum Negara Kesatuan Republik Indonesia.</p>

                <h6>9. Hukum yang Berlaku dan Kebijakan yang Relevan</h6>
                <p>Kebijakan Privasi ini diatur dan ditafsirkan berdasarkan hukum Negara Republik Indonesia. Peruri CA berkomitmen untuk melindungi Data Pribadi Anda dan mematuhi ketentuan peraturan perundang-undangan terkait perlindungan data pribadi. Peruri CA menyatakan untuk menghormati sepenuhnya semua hak yang ditetapkan dan dituangkan dalam hukum dan peraturan Indonesia:</p>
                <ul class="custom-list">
                    <li>
                        <span class="list-marker">a.</span>
                        <span class="list-content">Undang-Undang Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi;</span>
                    </li>
                    <li>
                        <span class="list-marker">b.</span>
                        <span class="list-content">Undang-Undang Nomor 11 Tahun 2008 tentang Informasi dan Transaksi Elektronik sebagaimana telah diubah dengan Undang-Undang Nomor 19 Tahun 2016 tentang Perubahan Atas Undang-Undang Nomor 11 Tahun 2008 tentang Informasi dan Transaksi Elektronik;</span>
                    </li>
                    <li>
                        <span class="list-marker">c.</span>
                        <span class="list-content">Peraturan Menteri Komunikasi dan Informatika Republik Indonesia Nomor 20 Tahun 2016 tentang Perlindungan Data Pribadi Dalam Sistem Elektronik; dan</span>  
                        </li>
                        <li>
                        <span class="list-marker">d.</span>
                        <span class="list-content">Undang-Undang Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi;</span>
                    </li>
                    <li>
                        <span class="list-marker">e.</span>
                        <span class="list-content">Peraturan Menteri Komunikasi dan Informatika Republik Indonesia Nomor 11 Tahun 2022 tentang Tata Kelola Penyelenggaraan Sertifikasi Elektronik.</span>
                    </li>
                    
                    
                   
                </ul>
                <p>Jika ada bagian dari Kebijakan Privasi ini yang dianggap tidak sah berrdasarkan hukum dan ketentuan peraturan perundang-undangan, ketentuan lain dalam Kebijakan Privasi ini tetap berlaku seutuhnya.</p>

                <h6>10. Pembatasan Tanggung Jawab</h6>
                <p>Sengketa yang terjadi antara Anda dengan Peruri CA sehubungan dengan ketentuan Keijakan Privasi ini, akan diselesaikan dengan cara:</p>
                
                
                <h6>11. Penyelesaian Sengketa</h6>
                <p>Peruri CA dapat menggunakan Data Pribadi Anda untuk tujuan lain yang tidak tercantum dalam Kebijakan Privasi ini, kecuali jika Anda memberikan persetujuan terlebih dahulu.</p>
                <ul class="custom-list">
                    <li>
                        <span class="list-marker">a.</span>
                        <span class="list-content">Salah satu pihak menyampaikan pemberitahuan secara tertulis kepada pihak lainnya. Penyelesaian sengketa wajib dilakukan dengan cara musyawarah dalam jangka waktu 30 (tiga puluh) kalender hari sejak pemberitahuan tertulis disampaikan.</span>
                    </li>
                    <li>
                        <span class="list-marker">b.</span>
                        <span class="list-content">Apabila sengketa tidak dapat diselesaikan dengan cara musyawarah, maka para pihak sepakat bahwa penyelesaian sengketa dilakukan oleh Badan Arbitrase Nasional Indonesia (BANI).</span>
                    </li>
                </ul>

                <div class="section">
                    <h6 class="section-title"><strong>12. Bahasa</strong></h6>
                        <div class="section-content">
                            <p class="section-intro">Kebijakan Privasi ini dibuat dalam 2 (dua) bahasa, yaitu Bahasa Indonesia dan Bahasa Inggris. Dalam hal terdapat ketidaksesuaian antara satu bahasa dengan bahasa yang lain, maka teks Bahasa Indonesia yang akan berlaku.</p>
        
                        </div>
                </div>

                <div class="section">
                    <h6 class="section-title"><strong>13. Perubahan pada Kebijakan Privasi</strong></h6>
                        <div class="section-content">
                            <p class="section-intro">Jika Peruri CA membuat perubahan materipada Kebijakan Privasi ini,Peruri CA akan memberi tahu Anda dengan mengunggah versi terbaru ke situs. Dokumen Kebijakan Privasidan setiap perubahan yang dilakukan dapat diakses secara publik dalam waktu selambat-lambatnya 7 (tujuh) hari kalender setelah disetujui. Setiap perubahan efektif berlaku 30 (tiga puluh) hari kalender setelah dipublikasikan.</p>
        
                        </div>
                </div>
                
                <div class="section">
                <h6 class="section-title"><strong>14. Hubungi kami</strong></h6>
                    <div class="section-content">
                        <p class="section-intro">Jika Anda memiliki pertanyaan tentang Kebijakan Privasi ini, silakan hubungi Peruri CA melalui:</p>
                        <div class="contact-info">
                            <table class="contact-table">
                                <tr>
                                            <td width="80">E-mail</td>
                                            <td width="10">:</td>
                                            <td>info.digital@peruri.co.id</td>
                                        </tr>
                                        <tr>
                                            <td>Telepon</td>
                                            <td>:</td>
                                            <td>+62 21 739 5000</td>
                                        </tr>
                                        <tr>
                                            <td>Alamat</td>
                                            <td>:</td>
                                            <td>Perum Peruri<br>
                                                Jl. Palatehan Blok KV No 4,<br>
                                                Kebayoran Baru, Jakarta 12160, Indonesia</td>
                                        </tr>
                                    </table>
                                </div>
                    </div>
                </div>
                              
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="agreePrivacyPolicy" disabled>Setuju</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>

document.addEventListener('DOMContentLoaded', function() {
    // Tampilkan modal saat halaman dimuat
    const termsModal = new bootstrap.Modal(document.getElementById('termsModal'));
    termsModal.show();

    // Fungsi untuk mengecek status checkbox
    function checkAgreements() {
        const privacyChecked = document.getElementById('privacyCheck').checked;
        const agreementChecked = document.getElementById('agreementCheck').checked;
        document.getElementById('agreeButton').disabled = !(privacyChecked && agreementChecked);
    }

    // Event listener untuk checkbox
    document.getElementById('privacyCheck').addEventListener('change', checkAgreements);
    document.getElementById('agreementCheck').addEventListener('change', checkAgreements);

    // Event listener untuk tombol Setuju
    document.getElementById('agreeButton').addEventListener('click', function() {
        termsModal.hide();
        // Tampilkan form registrasi
        document.querySelector('.container').style.display = 'block';
    });
});

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

document.addEventListener('DOMContentLoaded', function() {
    // Tampilkan modal saat halaman dimuat
    const termsModal = new bootstrap.Modal(document.getElementById('termsModal'));
    termsModal.show();

    // Fungsi untuk mengecek status checkbox
    function checkAgreements() {
        const privacyChecked = document.getElementById('privacyCheck').checked;
        const agreementChecked = document.getElementById('agreementCheck').checked;
        document.getElementById('agreeButton').disabled = !(privacyChecked && agreementChecked);
    }

    // Event listener untuk checkbox
    document.getElementById('privacyCheck').addEventListener('change', checkAgreements);
    document.getElementById('agreementCheck').addEventListener('change', checkAgreements);

    // Event listener untuk tombol Setuju
    document.getElementById('agreeButton').addEventListener('click', function() {
        termsModal.hide();
        // Tampilkan form registrasi
        document.querySelector('.container').style.display = 'block';
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const privacyCheck = document.getElementById('privacyCheck');
    const privacyPolicyModal = new bootstrap.Modal(document.getElementById('privacyPolicyModal'));
    
    // Event ketika checkbox privasi diklik
    privacyCheck.addEventListener('click', function(e) {
        e.preventDefault();
        privacyPolicyModal.show();
    });

    // Event ketika tombol Setuju diklik
    document.getElementById('agreePrivacyPolicy').addEventListener('click', function() {
        privacyCheck.checked = true;
        privacyPolicyModal.hide();
        checkAgreements();
    });

    // Event ketika tombol Kembali diklik
    document.querySelector('.modal-footer .btn-secondary').addEventListener('click', function() {
        window.history.back();
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const modalBody = document.querySelector('#privacyPolicyModal .modal-body');
    const agreeButton = document.getElementById('agreePrivacyPolicy');
    let hasReachedBottom = false;

    // Fungsi untuk mengecek apakah user sudah scroll sampai bawah
    function checkScrollPosition() {
        const scrollPosition = modalBody.scrollTop + modalBody.clientHeight;
        const scrollHeight = modalBody.scrollHeight;
        
        // Toleransi 10px untuk mengatasi perbedaan perhitungan di berbagai browser
        if (scrollHeight - scrollPosition <= 10) {
            hasReachedBottom = true;
            agreeButton.disabled = false;
        }
    }

    // Event listener untuk scroll
    modalBody.addEventListener('scroll', checkScrollPosition);

    // Reset status saat modal dibuka
    document.getElementById('privacyPolicyModal').addEventListener('show.bs.modal', function() {
        hasReachedBottom = false;
        agreeButton.disabled = true;
        modalBody.scrollTop = 0;
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

/* Sembunyikan container form saat awal */


/* Style untuk modal */
.modal-dialog-scrollable .modal-content {
    border-radius: 15px;
}

.modal-body {
    padding: 1.5rem;
}

.form-check {
    margin-top: 1rem;
}

.btn {
    padding: 0.5rem 1.5rem;
    border-radius: 8px;
}

#agreeButton:disabled {
    cursor: not-allowed;
}

/* Styling untuk modal kebijakan privasi */
.privacy-content {
    font-size: 14px;
    line-height: 1.6;
}

.privacy-content h6 {
    margin-top: 1.5rem;
    margin-bottom: 1rem;
    font-weight: 600;
}

.privacy-content ul {
    padding-left: 1.5rem;
}

.privacy-content li {
    margin-bottom: 0.5rem;
}

.modal-dialog-scrollable .modal-content {
    max-height: 90vh;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    border-top: 1px solid #dee2e6;
    padding: 1rem 2rem;
}

.modal-footer .btn {
    min-width: 120px;
    font-weight: 500;
}

.modal-footer .btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
}

.modal-footer .btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.modal-body ul {
    list-style-type: none;
    padding-left: 1.5rem;
}

.modal-body li {
    margin-bottom: 0.75rem;
    text-align: justify;
}

.modal-body li strong {
    margin-right: 0.5rem;
}

.section {
    margin-bottom: 1.5rem;
}

.section-title {
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
    color: #333;
    padding-left: 1px;
    display: flex;
    align-items: center;
}

.section-number {
    margin-right: 0.75rem; /* Menambah jarak antara nomor dan judul */
}

.section-heading {
    flex: 1;
}

.section-content {
    padding-left: 30px;
}

.section-intro {
    margin-bottom: 0.1rem; /* Dikurangi dari 1rem */
}

.custom-list {
    list-style: none;
    padding-left: 0;
    margin-top: 0.1rem; /* Ditambahkan untuk mengatur jarak dari paragraf di atasnya */
}

.custom-list li {
    display: flex;
    margin-bottom: 0.1rem; /* Dikurangi dari 0.75rem */
    align-items: flex-start;
}

.list-marker {
    flex: 0 0 30px;
    padding-right: 5px;
}

.list-content {
    flex: 1;
    padding-left: 5px;
}



.term {
    font-weight: 700;
    color: #333;
}

.contact-info {
    margin: 10px 0;
}

.contact-table {
    border-spacing: 0;
    border-collapse: collapse;
    line-height: 1.2; /* Mengurangi line height */
}

.contact-table td {
    padding: 2px 0; /* Mengurangi padding vertikal */
    vertical-align: top;
}

.contact-table br {
    line-height: 1.2; /* Mengurangi jarak antar baris pada alamat */
}

.modal-body {
    max-height: 70vh;
    overflow-y: auto;
}

#agreePrivacyPolicy:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

/* Tambahkan indikator scroll */
.modal-body::-webkit-scrollbar {
    width: 8px;
}

.modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.modal-body::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
    background: #555;
}



/* Styling untuk tabel kontak */
.contact-info {
    margin: 15px 0;
    margin-bottom: 0.1rem;
}

.contact-table {
    border-spacing: 0;
    border-collapse: collapse;
}

.contact-table td {
    padding: 5px 0;
    vertical-align: top;
}

.text-blue {
        color: blue;
        font-weight: bold; /* Opsional, jika ingin teks lebih menonjol */
    }

</style>
@endpush 