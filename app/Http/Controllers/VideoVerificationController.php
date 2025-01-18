<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PeruriService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class VideoVerificationController extends Controller
{
    public function __construct(protected PeruriService $peruriService)
    {
    }

    public function index()
    {
        return view('video_verification');
    }

    public function verify(Request $request)
    {
        try {
            $email = $request->input('email');
            if (!$email) {
                throw new \Exception('Email tidak ditemukan');
            }

            Log::info('Starting video verification process', [
                'email' => $email
            ]);

            // Validate video data
            $videoData = $request->input('video');
            if (!$videoData) {
                throw new \Exception('Data video tidak ditemukan');
            }

            // Extract pure base64 data without MIME
            $base64Video = '';
            if (strpos($videoData, 'base64,') !== false) {
                $base64Video = explode('base64,', $videoData)[1];
            } else {
                $base64Video = $videoData;
            }

            // Validate base64 string
            if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $base64Video)) {
                throw new \Exception('Format base64 video tidak valid');
            }

            // Verify video size
            $decodedLength = strlen(base64_decode($base64Video));
            if ($decodedLength > 10 * 1024 * 1024) { // 10MB limit
                throw new \Exception('Ukuran video terlalu besar (maksimal 10MB)');
            }

            if ($decodedLength < 100 * 1024) { // 100KB minimum
                throw new \Exception('Ukuran video terlalu kecil (minimal 100KB)');
            }

            Log::info('Sending video for verification', [
                'email' => $email,
                'videoSize' => strlen($base64Video)
            ]);

            // Send to Peruri service with correct parameter name (videoStream)
            $response = $this->peruriService->verifyVideo([
                'email' => $email,
                'videoStream' => $base64Video  // Changed from 'video' to 'videoStream'
            ]);

            if ($response['success']) {
                DB::table('akun_peruri')
                    ->where('email', $email)
                    ->update([
                        'video_verification_status' => $response['status'] ?? 'PENDING_VERIFICATION',
                        'video_verification_date' => now(),
                        'updated_at' => now()
                    ]);
            }

            Log::info('Video verification completed', [
                'email' => $email,
                'success' => $response['success'] ?? false,
                'status' => $response['status'] ?? null
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Video verification error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkEmailStatus(Request $request)
    {
        try {
            $email = $request->input('email');
            
            if (!$email) {
                throw new \Exception('Email tidak ditemukan');
            }

            Log::info('Checking email status', ['email' => $email]);

            // Cek status email di database
            $userData = DB::table('akun_peruri')
                ->where('email', $email)
                ->first();

            if (!$userData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email tidak terdaftar'
                ]);
            }

            // Pastikan status sudah registered
            if ($userData->status !== 'registered') {
                return response()->json([
                    'success' => false,
                    'message' => 'Email belum teregistrasi di Peruri'
                ]);
            }

            // Set email ke session untuk digunakan di verify
            session(['registration_email' => $email]);

            return response()->json([
                'success' => true,
                'message' => 'Email valid',
                'data' => [
                    'email' => $email,
                    'status' => $userData->status
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Email status check error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }
}
