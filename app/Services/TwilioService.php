<?php

namespace App\Services;

use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;
use Exception;
use Illuminate\Support\Facades\Log;

class TwilioService
{
    protected $client;
    protected $verifyService;

    public function __construct()
    {
        try {
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $this->verifyService = config('services.twilio.verify_sid');

            Log::debug('Initializing Twilio with:', [
                'sid' => $sid,
                'token' => substr($token, 0, 6) . '...',
                'verify_sid' => $this->verifyService
            ]);

            if (empty($sid) || empty($token) || empty($this->verifyService)) {
                throw new Exception('Twilio credentials not properly configured');
            }

            $this->client = new Client($sid, $token);
            Log::info('Twilio client initialized successfully');

        } catch (TwilioException $e) {
            Log::error('Twilio initialization error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            throw $e;
        }
    }

    public function sendOTP($phoneNumber)
    {
        try {
            Log::info('Attempting to send OTP', [
                'phone' => $phoneNumber,
                'verify_sid' => $this->verifyService
            ]);

            $verification = $this->client->verify->v2
                ->services($this->verifyService)
                ->verifications
                ->create(
                    $phoneNumber,
                    "sms",
                    [
                        "locale" => "id",
                        "channel" => "sms"
                    ]
                );

            // Log full response for debugging
            Log::info('Twilio Response:', [
                'status' => $verification->status,
                'sid' => $verification->sid,
                'service_sid' => $verification->serviceSid,
                'channel' => $verification->channel,
                'to' => $verification->to,
                'valid' => $verification->valid,
                'date_created' => $verification->dateCreated,
                'date_updated' => $verification->dateUpdated
            ]);

            if ($verification->status !== 'pending') {
                throw new Exception('OTP sending failed with status: ' . $verification->status);
            }

            return [
                'success' => true,
                'status' => $verification->status,
                'channel' => $verification->channel,
                'to' => $verification->to,
                'created' => $verification->dateCreated->format('Y-m-d H:i:s'),
                'verification_sid' => $verification->sid
            ];

        } catch (TwilioException $e) {
            Log::error('Twilio send OTP error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'phone' => $phoneNumber
            ]);
            throw new Exception('Failed to send OTP: ' . $e->getMessage());
        }
    }

    public function verifyOTP($phoneNumber, $code)
    {
        try {
            Log::info('Verifying OTP', [
                'phone' => $phoneNumber,
                'code_length' => strlen($code)
            ]);

            $verification_check = $this->client->verify->v2
                ->services($this->verifyService)
                ->verificationChecks
                ->create([
                    'to' => $phoneNumber,
                    'code' => $code
                ]);
            
            Log::info('Verification check response:', [
                'status' => $verification_check->status,
                'valid' => $verification_check->valid,
                'sid' => $verification_check->sid,
                'to' => $verification_check->to,
                'date_created' => $verification_check->dateCreated,
                'date_updated' => $verification_check->dateUpdated
            ]);

            return [
                'success' => $verification_check->status === 'approved',
                'status' => $verification_check->status,
                'valid' => $verification_check->valid,
                'to' => $verification_check->to,
                'date_verified' => $verification_check->dateUpdated->format('Y-m-d H:i:s')
            ];

        } catch (TwilioException $e) {
            Log::error('Twilio verify OTP error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'phone' => $phoneNumber
            ]);
            throw new Exception('Failed to verify OTP: ' . $e->getMessage());
        }
    }
} 