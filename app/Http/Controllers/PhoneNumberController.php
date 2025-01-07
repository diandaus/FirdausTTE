<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PhoneNumberController extends Controller
{
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
                ], 200); // Tambahkan status code
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ], 404); // Tambahkan status code

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
            // Validasi input jika diperlukan
            $request->validate([
                'email' => 'required|email',
                'phone' => 'required',
                // tambahkan validasi lain sesuai kebutuhan
            ]);

            // Proses data yang dikirim
            // Misalnya update status atau tambahkan ke tabel lain
            $updated = DB::table('akun_peruri')
                ->where('phone', $request->phone)
                ->update([
                    'status' => 'verified',
                    'updated_at' => now()
                ]);

            if ($updated) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data berhasil diproses!'
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memproses data!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }
}