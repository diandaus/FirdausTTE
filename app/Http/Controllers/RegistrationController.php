<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Services\PeruriService;
use Illuminate\Support\Facades\Session;

class RegistrationController extends Controller
{
    public function __construct(protected PeruriService $peruriService)
    {
    }

    public function index()
    {
        return view('registration.index', [
            'showTerms' => true
        ]);
    }

    public function store(Request $request)
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'email' => 'required|email|max:255',
                'ktp' => 'required|string|size:16',
                'ktpPhoto' => 'required|image|max:2048',
                'address' => 'required|string',
                'city' => 'required|string',
                'province' => 'required|string',
                'gender' => 'required|in:M,F',
                'placeOfBirth' => 'required|string',
                'dateOfBirth' => 'required|date',
                'orgUnit' => 'nullable|string',
                'workUnit' => 'nullable|string',
                'position' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed', [
                    'errors' => $validator->errors(),
                    'input' => $request->all(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Convert images to base64 (only if they exist)
            $ktpPhotoBase64 = base64_encode(file_get_contents($request->file('ktpPhoto')));
            $selfPhotoBase64 = $request->hasFile('selfPhoto') 
                ? base64_encode(file_get_contents($request->file('selfPhoto')))
                : '';
            $npwpPhotoBase64 = $request->hasFile('npwpPhoto')
                ? base64_encode(file_get_contents($request->file('npwpPhoto')))
                : '';

            // Panggil service Peruri
            $response = $this->peruriService->register([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'ktp' => $request->ktp,
                'ktpPhoto' => $ktpPhotoBase64,
                'address' => $request->address,
                'city' => $request->city,
                'province' => $request->province,
                'gender' => $request->gender,
                'placeOfBirth' => $request->placeOfBirth,
                'dateOfBirth' => $request->dateOfBirth,
                'orgUnit' => $request->orgUnit,
                'workUnit' => $request->workUnit,
                'position' => $request->position
            ]);

            // Simpan nomor telepon ke session
            Session::put('phone_number', $request->phone);

            // Redirect ke halaman verifikasi
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dikirim untuk registrasi.',
            ]);
        } catch (\Exception $e) {
            Log::error('Registration Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
