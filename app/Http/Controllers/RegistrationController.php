<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Services\PeruriService;


class RegistrationController extends Controller
{
    public function __construct(protected PeruriService $peruriService)
    {
    }

    public function index()
    {
        return view('registration.index');
    }

    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8',
            'ktp' => 'required|string|size:16',
            'ktpPhoto' => 'required|image|max:2048',
            'npwp' => 'required|string|max:20',
            'npwpPhoto' => 'required|image|max:2048',
            'selfPhoto' => 'required|image|max:2048',
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
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Convert images to base64
            $ktpPhotoBase64 = base64_encode(file_get_contents($request->file('ktpPhoto')));
            $npwpPhotoBase64 = base64_encode(file_get_contents($request->file('npwpPhoto')));
            $selfPhotoBase64 = base64_encode(file_get_contents($request->file('selfPhoto')));

            $response = $this->peruriService->register([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => $request->password,
                'type' => 'INDIVIDUAL',
                'ktp' => $request->ktp,
                'ktpPhoto' => $ktpPhotoBase64,
                'npwp' => $request->npwp,
                'npwpPhoto' => $npwpPhotoBase64,
                'selfPhoto' => $selfPhotoBase64,
                'address' => $request->address,
                'city' => $request->city,
                'province' => $request->province,
                'gender' => $request->gender,
                'placeOfBirth' => $request->placeOfBirth,
                'dateOfBirth' => $request->dateOfBirth,
                'orgUnit' => $request->orgUnit,
                'workUnit' => $request->workUnit,
                'position' => $request->position,
            ]);

            if ($response['success']) {
                session(['registration_email' => $request->email]);
                
                return redirect()->route('registration.index')
                                ->with('success', 'Registrasi berhasil!');
            }

            Log::error('Registration API Error', [
                'response' => $response
            ]);

            return redirect()
                ->route('registration.index')
                ->withInput()
                ->with('error', $response['message'] ?? 'Unknown error');

        } catch (\Exception $e) {
            Log::error('Registration Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                            ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                            ->withInput();
        }
    }
}
