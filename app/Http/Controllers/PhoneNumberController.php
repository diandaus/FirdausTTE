<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\PeruriService;

class PhoneNumberController extends Controller
{
    protected $peruriService;

    public function __construct(PeruriService $peruriService)
    {
        $this->peruriService = $peruriService;
    }

    public function index()
    {
        return view('phone-form');
    }

    public function fetchData(Request $request)
    {
        try {
            $phone = $request->input('phone');
            
            // Debug log
            \Log::info('Searching phone: ' . $phone);

            $data = DB::table('akun_peruri')
                     ->where('phone', $phone)
                     ->first();

            \Log::info('Query result:', ['data' => $data]);

            if ($data) {
                return response()->json([
                    'status' => 'success',
                    'data' => $data
                ]);
            }

            // Ubah response untuk data tidak ditemukan
            return response()->json([
                'status' => 'not_found',
                'message' => 'Data tidak ditemukan'
            ], 200); // Gunakan 200 bukan 404

        } catch (\Exception $e) {
            \Log::error('Error in fetchData: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function submit(Request $request)
    {
        try {
            \Log::info('Submit request received', ['phone' => $request->phone]);
            
            // Get complete data from database
            $userData = DB::table('akun_peruri')
                ->where('phone', $request->phone)
                ->first();

            if (!$userData) {
                \Log::warning('Data not found for phone', ['phone' => $request->phone]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            \Log::info('User data found', ['userData' => $userData]);

            // Prepare data for Peruri registration
            $registrationData = [
                'name' => $userData->name,
                'phone' => $userData->phone,
                'email' => $userData->email,
                'ktp' => $userData->ktp,
                'ktpPhoto' => $userData->ktp_photo ?? '',
                'address' => $userData->address,
                'city' => $userData->city,
                'province' => $userData->province,
                'gender' => $userData->gender,
                'placeOfBirth' => $userData->place_of_birth,
                'dateOfBirth' => $userData->date_of_birth,
                'orgUnit' => $userData->org_unit,
                'workUnit' => $userData->work_unit,
                'position' => $userData->position,
            ];

            \Log::info('Calling Peruri registration service', ['data' => $registrationData]);

            // Register with Peruri
            $registrationResult = $this->peruriService->register($registrationData);

            \Log::info('Peruri registration result', ['result' => $registrationResult]);

            if ($registrationResult['success']) {
                // Create record in peruri_registration table
                DB::table('peruri_registrations')->insert([
                    'name' => $userData->name,
                    'email' => $userData->email,
                    'registered_at' => now(),
                    'registration_response' => json_encode($registrationResult['data']),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                \Log::info('Registration successful, sending response', [
                    'email' => $userData->email,
                    'response' => $registrationResult['data']
                ]);

                return response()
                    ->json([
                        'status' => 'success',
                        'message' => 'Registrasi berhasil!',
                        'data' => [
                            'email' => $userData->email,
                            'peruri_response' => $registrationResult['data']
                        ]
                    ])
                    ->header('Content-Type', 'application/json')
                    ->header('X-Debug-Info', 'none');
            }

            \Log::warning('Registration failed', ['result' => $registrationResult]);
            return response()
                ->json([
                    'status' => 'error',
                    'message' => $registrationResult['message'] ?? 'Gagal melakukan registrasi'
                ], 400)
                ->header('Content-Type', 'application/json');

        } catch (\Exception $e) {
            \Log::error('Registration error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()
                ->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500)
                ->header('Content-Type', 'application/json');
        }
    }
}