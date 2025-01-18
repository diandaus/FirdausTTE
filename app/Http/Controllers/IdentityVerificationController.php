<?php

namespace App\Http\Controllers;

use App\Services\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Exception;

class IdentityVerificationController extends Controller
{
    /**
     * Show the verification form
     */
    public function index()
    {
        // Ambil nomor telepon dari session yang disimpan saat registrasi
        $phone = Session::get('phone_number', '');
        
        // Jika nomor telepon ada, ambil 4 digit terakhir
        $lastDigits = '';
        if ($phone) {
            $lastDigits = substr($phone, -4);
        }
        
        $error = Session::get('verification_error', '');
        
        return view('verify-identity', [
            'phone' => $phone,
            'lastDigits' => $lastDigits,
            'error' => $error
        ]);
    }

    /**
     * Verify the submitted code
     */
    public function verify(Request $request)
    {
        $request->validate([
            'verificationCode' => 'required|string|size:6',
            'rememberBrowser' => 'boolean'
        ]);

        try {
            // Add your verification logic here
            // Example: Check code against stored code or verify with SMS service

            if ($request->rememberBrowser) {
                // Handle "remember browser" functionality
                // Example: Set a cookie or store in database
            }

            // Hapus session setelah berhasil
            Session::forget('phone_number');
            
            return redirect()->route('dashboard')->with('success', 'Identity verified successfully');
            
        } catch (\Exception $e) {
            return back()->with('verification_error', 'Invalid verification code. Please try again.');
        }
    }

    /**
     * Resend verification code
     */
    public function resend(Request $request)
    {
        try {
            // Add your code resending logic here
            // Example: Generate new code and send SMS

            return back()->with('success', 'New verification code sent');
            
        } catch (\Exception $e) {
            return back()->with('verification_error', 'Could not send verification code. Please try again later.');
        }
    }

    /**
     * Request verification via phone call
     */
    public function requestCall(Request $request)
    {
        try {
            // Add your phone call verification logic here
            // Example: Initiate phone call with verification code

            return back()->with('success', 'You will receive a call shortly');
            
        } catch (\Exception $e) {
            return back()->with('verification_error', 'Could not initiate verification call. Please try again later.');
        }
    }

    /**
     * Reset MFA settings
     */
    public function resetMFA(Request $request)
    {
        try {
            // Add your MFA reset logic here
            // Example: Reset user's MFA settings in database

            return redirect()->route('mfa.setup')->with('success', 'MFA has been reset. Please set up new verification method.');
            
        } catch (\Exception $e) {
            return back()->with('verification_error', 'Could not reset MFA. Please contact support.');
        }
    }
}
