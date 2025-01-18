<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Identity</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f5f5f5;
        }
        .verification-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .twilio-logo {
            width: 120px;
            margin-bottom: 20px;
        }
        .error-message {
            background-color: #dc3545;
            color: white;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .phone-display {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .continue-btn {
            width: 100%;
            margin-top: 15px;
        }
        .footer-links {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            gap: 20px;
        }
        .logo-divider {
            height: 40px;
            width: 1px;
            background-color: #dee2e6;
        }
        .logo {
            height: 40px;
            object-fit: contain;
        }
        .text-center-section {
            text-align: center;
            margin-bottom: 20px;
        }

        .text-center-section h4 {
            margin-bottom: 10px;
        }

        .input-wrapper {
            position: relative;
            margin-bottom: 1rem;
        }

        .text-wrapper {
            position: relative;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .form-control {
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border-radius: 4px;
            border: 1px solid #ced4da;
            width: 100%;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .form-floating {
            position: relative;
        }

        .form-floating > .form-control {
            height: calc(3.5rem + 2px);
            padding: 1rem 0.75rem;
        }

        .form-floating > label {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            padding: 1rem 0.75rem;
            pointer-events: none;
            border: 1px solid transparent;
            transform-origin: 0 0;
            transition: opacity .1s ease-in-out, transform .1s ease-in-out;
        }

        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            opacity: .65;
            transform: scale(.85) translateY(-0.5rem) translateX(0.15rem);
        }

        .form-floating > .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .form-control {
            border-radius: 4px;
            border: 1px solid #ced4da;
            width: 100%;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="verification-container">
            <!-- Replace the old logo section with this new one -->
            <div class="logo-container">
                <img src="{{ asset('images/logors.png') }}" 
                     alt="Logo Rumah Sakit" 
                     class="logo">
                
                <div class="logo-divider"></div>
                
                <img src="{{ asset('images/logo.png') }}" 
                     alt="Logo Peruri" 
                     class="logo">
            </div>
            
            <div class="text-center-section">
                <h4>Verifikasi Identitas Anda</h4>
                <p>Kami telah mengirimkan SMS ke:</p>
            </div>


            <!-- Phone Number Display -->
            <div class="phone-display text-center">
                @if($lastDigits)
                    Nomor telepon berakhir {{ $lastDigits }}
                @else
                Nomor telepon berakhir 1390
                @endif
            </div>

            <!-- Verification Form -->
            <form action="{{ route('verify.identity.check') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <div class="input-wrapper">
                        <input type="text" 
                               class="form-control @error('verificationCode') is-invalid @enderror" 
                               id="verificationCode"
                               name="verificationCode"
                               maxlength="6" 
                               placeholder="Enter the 6-digit code*"
                               required
                               autocomplete="off"
                               autocapitalize="none"
                               spellcheck="false"
                               autofocus>
                        
                        @error('verificationCode')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary continue-btn">Continue</button>
            </form>

            <!-- Helper Links -->
            <div class="mt-3">
                <small>
                    Tidak menerima kode? 
                    <a href="{{ route('verify.identity.resend') }}">Kirim Ulang</a>
                </small>
            </div>

            <!-- Footer -->
            <div class="footer-links">
                <small>
                    <a href="#">Syarat dan Ketentuan</a> | <a href="#">Kebijakan Privasi</a><br>
                    © 2025 Ibnusina Inc. all rights reserved
                </small>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
  