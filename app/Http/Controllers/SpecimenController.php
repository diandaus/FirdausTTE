<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\PeruriService;

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
            $request->validate([
                'specimen' => 'required_without:signature_data|image|max:2048',
                'signature_data' => 'required_without:specimen'
            ]);

            $email = session('registration_email');
            if (!$email) {
                throw new \Exception('Email tidak ditemukan');
            }

            if ($request->has('signature_data')) {
                $imageBase64 = preg_replace('/^data:image\/(png|jpeg|jpg);base64,/', '', $request->signature_data);
            } else {
                $imageBase64 = base64_encode(file_get_contents($request->specimen));
            }
            
            $response = $this->peruriService->sendSpecimen([
                'email' => $email,
                'specimen' => $imageBase64
            ]);

            // Return JSON response
            return response()->json([
                'success' => $response['success'],
                'message' => $response['message']
            ]);

        } catch (\Exception $e) {
            Log::error('Specimen Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim specimen: ' . $e->getMessage()
            ], 422);
        }
    }
} 