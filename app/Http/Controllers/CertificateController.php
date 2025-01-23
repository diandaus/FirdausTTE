<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PeruriService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CertificateController extends Controller
{
    protected $peruriService;

    public function __construct(PeruriService $peruriService)
    {
        $this->peruriService = $peruriService;
    }

    public function showCheckForm()
    {
        return view('check_certificate');
    }

    public function checkCertificate(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:13'
        ]);

        try {
            // Cari email berdasarkan nomor telepon di database
            $user = DB::table('akun_peruri')
                     ->where('phone', $request->phone)
                     ->first();

            if (!$user) {
                return back()->with('result', [
                    'valid' => false,
                    'message' => 'Nomor telepon tidak terdaftar dalam sistem.'
                ]);
            }

            // Cek status sertifikat menggunakan PeruriService
            $certificateStatus = $this->peruriService->checkCertificateStatus($user->email);

            Log::info('Certificate Status Check Result', [
                'phone' => $request->phone,
                'email' => $user->email,
                'status' => $certificateStatus
            ]);

            // Cek kondisi "Not Yet KYC"
            if (isset($certificateStatus['message']) && 
                strtolower($certificateStatus['message']) === 'not yet kyc, 0 times') {
                $result = [
                    'valid' => false,
                    'needs_verification' => true,
                    'message' => 'Not Yet KYC, 0 Times',
                    'name' => $user->name,
                    'email' => $user->email,
                    'data' => $certificateStatus
                ];
            } else if ($certificateStatus['success']) {
                $result = [
                    'valid' => true,
                    'message' => 'Sertifikat ditemukan',
                    'name' => $user->name,
                    'email' => $user->email,
                    'data' => $certificateStatus
                ];
            } else {
                $result = [
                    'valid' => false,
                    'message' => $certificateStatus['message'] ?? 'Gagal memeriksa status sertifikat',
                    'name' => $user->name,
                    'email' => $user->email,
                    'data' => $certificateStatus
                ];
            }

            return back()->with('result', $result);

        } catch (\Exception $e) {
            Log::error('Certificate Check Error', [
                'message' => $e->getMessage(),
                'phone' => $request->phone
            ]);

            return back()->with('result', [
                'valid' => false,
                'message' => 'Terjadi kesalahan saat memeriksa sertifikat.'
            ]);
        }
    }
} 