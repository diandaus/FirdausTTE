<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\PeruriService;
use Illuminate\Support\Facades\DB;
use Exception;

class SpecimenController extends Controller
{
    protected $peruriService;

    public function __construct(PeruriService $peruriService)
    {
        $this->peruriService = $peruriService;
    }

    public function index()
    {
        return view('specimen.index');
    }

    public function send(Request $request)
    {
        try {
            $email = session('registration_email');
            if (!$email) {
                throw new Exception('Email tidak ditemukan dalam session');
            }

            // Get specimen data (from canvas or file upload)
            $specimenBase64 = '';
            if ($request->has('signature_data')) {
                // Remove MIME type prefix from canvas data
                if (strpos($request->input('signature_data'), 'base64,') !== false) {
                    $specimenBase64 = explode('base64,', $request->input('signature_data'))[1];
                } else {
                    $specimenBase64 = $request->input('signature_data');
                }
            } elseif ($request->hasFile('specimen')) {
                // Validate image file
                $file = $request->file('specimen');
                $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg'];
                
                if (!in_array($file->getMimeType(), $allowedMimes)) {
                    throw new Exception('Format file harus berupa JPG atau PNG');
                }

                // Convert to base64 without MIME prefix
                $specimenBase64 = base64_encode(file_get_contents($file));
            }

            if (empty($specimenBase64)) {
                throw new Exception('Data specimen tidak ditemukan');
            }

            // Validate base64 string
            if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $specimenBase64)) {
                throw new Exception('Format base64 tidak valid');
            }

            Log::info('Sending specimen', [
                'email' => $email,
                'base64_length' => strlen($specimenBase64)
            ]);

            $response = $this->peruriService->sendSpecimen([
                'email' => $email,
                'specimen' => $specimenBase64
            ]);

            if ($response['success']) {
                // Update dengan kolom yang benar
                DB::table('akun_peruri')
                    ->where('email', $email)
                    ->update([
                        'specimen_status' => $response['status'] ?? 'VERIFIED',
                        'specimen_date' => now(),
                        'specimen_data' => $specimenBase64, // Simpan data specimen
                        'updated_at' => now()
                    ]);
            }

            return response()->json($response);

        } catch (Exception $e) {
            Log::error('Specimen submission error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim specimen: ' . $e->getMessage()
            ], 500);
        }
    }
} 