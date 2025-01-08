<?php

namespace App\Http\Controllers;

use App\Services\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class PhoneVerificationController extends Controller
{
    protected $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    public function sendOTP(Request $request)
    {
        try {
            Log::info('=== Starting OTP Send Process ===');
            
            $request->validate([
                'phone_number' => 'required|string|regex:/^([0-9\s\-\+\(\)]*)$/|min:10'
            ]);

            $phoneNumber = $this->formatPhoneNumber($request->phone_number);
            Log::info('Formatted phone number', [
                'original' => $request->phone_number,
                'formatted' => $phoneNumber
            ]);

            $result = $this->twilioService->sendOTP($phoneNumber);
            Log::info('OTP process completed', $result);
            
            return response()->json([
                'success' => true,
                'message' => 'OTP telah dikirim ke nomor ' . $phoneNumber,
                'data' => $result
            ]);
            
        } catch (Exception $e) {
            Log::error('Error in sendOTP:', [
                'message' => $e->getMessage(),
                'class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim OTP',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyOTP(Request $request)
    {
        try {
            $request->validate([
                'phone_number' => 'required|string',
                'otp' => 'required|string|size:6'
            ]);

            $phoneNumber = $this->formatPhoneNumber($request->phone_number);
            $result = $this->twilioService->verifyOTP($phoneNumber, $request->otp);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Verifikasi OTP berhasil',
                    'data' => $result
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Kode OTP tidak valid',
                'data' => $result
            ], 400);

        } catch (Exception $e) {
            Log::error('Error in verifyOTP:', [
                'message' => $e->getMessage(),
                'class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memverifikasi OTP',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format nomor telepon ke format internasional
     * 
     * @param string $phoneNumber
     * @return string
     */
    private function formatPhoneNumber($phoneNumber)
    {
        // Hapus semua karakter non-digit
        $number = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Jika dimulai dengan 0, ganti dengan +62
        if (substr($number, 0, 1) === '0') {
            $number = '+62' . substr($number, 1);
        }
        
        // Jika belum ada +62, tambahkan
        if (substr($number, 0, 2) === '62') {
            $number = '+' . $number;
        }
        
        // Jika belum ada format internasional sama sekali
        if (substr($number, 0, 1) !== '+') {
            $number = '+62' . $number;
        }
        
        \Log::info('Formatted phone number', [
            'original' => $phoneNumber,
            'formatted' => $number
        ]);
        
        return $number;
    }
} 