<?php

namespace App\Http\Controllers;

use App\Services\PeruriService;
use Illuminate\Http\Request;

class AkunPeruriController extends Controller
{
    protected $peruriService;

    public function __construct(PeruriService $peruriService)
    {
        $this->peruriService = $peruriService;
    }

    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required',
                'phone' => 'required',
                'email' => 'required|email',
                'password' => 'required',
                'ktp' => 'required',
                'ktpPhoto' => 'required',
                'npwp' => 'required',
                'npwpPhoto' => 'required',
                'selfPhoto' => 'required',
                'address' => 'required',
                'city' => 'required',
                'province' => 'required',
                'gender' => 'required|in:M,F',
                'placeOfBirth' => 'required',
                'dateOfBirth' => 'required|date',
                'orgUnit' => 'required',
                'workUnit' => 'required',
                'position' => 'required',
            ]);

            $response = $this->peruriService->register($validatedData);
            
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}