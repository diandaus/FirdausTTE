<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PeruriService;
use Illuminate\Support\Facades\Log;

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
            $email = session('registration_email');
            if (!$email) {
                throw new \Exception('Email tidak ditemukan');
            }

            // Ambil data video dari request body
            $videoData = $request->input('video');
            if (!$videoData) {
                throw new \Exception('Data video tidak ditemukan');
            }

            // Hapus header data URI jika ada
            $base64Video = str_contains($videoData, 'base64,') 
                ? explode('base64,', $videoData)[1] 
                : $videoData;

            $response = $this->peruriService->verifyVideo([
                'email' => $email,
                'video' => $base64Video
            ]);

            Log::info('Video verification response', ['response' => $response]);

            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('Video verification error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
