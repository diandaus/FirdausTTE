<?php

use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\VideoVerificationController;
use App\Http\Controllers\OCRController;
use App\Http\Controllers\AkunPeruriController;
use App\Http\Controllers\SpecimenController;
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

// Akun Peruri Routes - menggunakan resource
Route::resource('akun-peruri', AkunPeruriController::class)->only([
    'index', 'create', 'store', 'show'
]);

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
