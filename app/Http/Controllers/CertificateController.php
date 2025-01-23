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

            if ($certificateStatus['success']) {
                $result = [
                    'valid' => true,
                    'message' => 'Sertifikat ditemukan',
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $certificateStatus['status'],
                    'data' => $certificateStatus['data']
                ];
            } else {
                $result = [
                    'valid' => false,
                    'message' => $certificateStatus['message'] ?? 'Gagal memeriksa status sertifikat'
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