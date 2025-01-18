<?php

use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\VideoVerificationController;
use App\Http\Controllers\OCRController;
use App\Http\Controllers\AkunPeruriController;
use App\Http\Controllers\SpecimenController;
use App\Http\Controllers\PhoneNumberController;
use App\Http\Controllers\PhoneVerificationController;
use App\Http\Controllers\IdentityVerificationController;
use Illuminate\Support\Facades\Route;

// Registration Routes
Route::get('/', [RegistrationController::class, 'index'])->name('home');
Route::get('/registration', [RegistrationController::class, 'index'])->name('registration.index');
Route::post('/registration', [RegistrationController::class, 'store'])->name('registration.store');

// Video Verification Routes
Route::get('/video-verification', [VideoVerificationController::class, 'index'])
    ->name('video-verification.index');
Route::post('/video-verification/verify', [VideoVerificationController::class, 'verify'])
    ->name('video.verify');

// OCR Route
Route::post('/ocr-ktp', [OCRController::class, 'processKTP'])->name('ocr.ktp');

// Specimen Routes
Route::get('/specimen', [SpecimenController::class, 'index'])->name('specimen.index');
Route::post('/specimen/send', [SpecimenController::class, 'send'])->name('specimen.send');

// No Telepon 
Route::get('/phone', [PhoneNumberController::class, 'index'])->name('phone.index');
Route::post('/phone/fetch', [PhoneNumberController::class, 'fetchData'])->name('phone.fetch');
Route::post('/phone/submit', [PhoneNumberController::class, 'submit'])
    ->name('phone.submit')
    ->middleware(['web']);

// Akun Peruri Routes - menggunakan resource
Route::resource('akun-peruri', AkunPeruriController::class)->only([
    'index', 'create', 'store', 'show'
]);

// Phone Verification Routes
Route::post('/verify/send-otp', [PhoneVerificationController::class, 'sendOTP'])->name('verify.send-otp');
Route::post('/verify/verify-otp', [PhoneVerificationController::class, 'verifyOTP'])->name('verify.verify-otp');

// Identity Verification Routes with throttle middleware
Route::middleware(['throttle:6,1'])->group(function () {
    Route::post('/verify-identity/check', [IdentityVerificationController::class, 'verify'])->name('verify.identity.check');
    Route::post('/verify-identity/resend', [IdentityVerificationController::class, 'resend'])->name('verify.identity.resend');
    Route::post('/verify-identity/call', [IdentityVerificationController::class, 'requestCall'])->name('verify.identity.call');
});

// Routes without throttle
Route::get('/verify-identity', [IdentityVerificationController::class, 'index'])->name('verify.identity');
Route::post('/verify-identity/reset-mfa', [IdentityVerificationController::class, 'resetMFA'])->name('verify.identity.reset-mfa');

// Tambahkan route untuk pengecekan email
Route::post('/check-email-status', [VideoVerificationController::class, 'checkEmailStatus'])
    ->name('check.email.status');

// Debug routes hanya jika dalam mode debug
if (config('app.debug')) {
    Route::get('/routes', function() {
        $routeCollection = Route::getRoutes();
        echo "<table style='width:100%'>";
        echo "<tr>";
        echo "<td width='10%'><h4>HTTP Method</h4></td>";
        echo "<td width='10%'><h4>Route</h4></td>";
        echo "<td width='10%'><h4>Name</h4></td>";
        echo "<td width='70%'><h4>Corresponding Action</h4></td>";
        echo "</tr>";
        foreach ($routeCollection as $value) {
            echo "<tr>";
            echo "<td>" . implode('|', $value->methods()) . "</td>";
            echo "<td>" . $value->uri() . "</td>";
            echo "<td>" . $value->getName() . "</td>";
            echo "<td>" . $value->getActionName() . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    });
}
