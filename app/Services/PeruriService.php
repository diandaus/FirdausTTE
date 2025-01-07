<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class PeruriService
{
    protected $baseUrl;
    protected $systemId;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.digital_signature.url'), '/');
        $this->systemId = config('services.digital_signature.system_id');
        $this->apiKey = config('services.digital_signature.key');
    }

    public function getJwtToken()
    {
        try {
            if (Cache::has('peruri_jwt_token')) {
                return Cache::get('peruri_jwt_token');
            }

            $response = Http::withHeaders([
                'x-Gateway-APIKey' => $this->apiKey,
                'Content-Type' => 'application/json'
            ])
            ->withOptions([
                'verify' => false,
                'timeout' => 30
            ])
            ->post($this->baseUrl . '/jwtSandbox/1.0/getJsonWebToken/v1', [
                'param' => [
                    'systemId' => $this->systemId
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['data']['jwt'])) {
                    $token = $data['data']['jwt'];
                    Cache::put('peruri_jwt_token', $token, now()->addMinutes(55));
                    return $token;
                }
            }

            throw new Exception('Failed to get JWT token: ' . $response->body());
        } catch (Exception $e) {
            Log::error('JWT Token Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function verifyVideo(array $data)
    {
        try {
            // Dapatkan token JWT
            $token = $this->getJwtToken();
            
            $url = $this->baseUrl . '/digitalSignatureFullJwtSandbox/1.0/videoVerification/v1';
            
            $response = Http::withHeaders([
                'x-Gateway-APIKey' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ])
            ->withOptions([
                'verify' => false,
                'timeout' => 30
            ])
            ->post($url, [
                'param' => [
                    'email' => $data['email'],
                    'systemId' => $this->systemId,
                    'videoStream' => $data['video']
                ]
            ]);

            $result = $response->json();
            Log::info('Video verification response', ['response' => $result]);

            return [
                'success' => $response->successful(),
                'data' => $result
            ];

        } catch (Exception $e) {
            Log::error('Video verification service error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function register($data)
    {
        try {
            $token = $this->getJwtToken();
            $registrationResponse = $this->sendRegistrationRequest($data, $token);

            if (!is_array($registrationResponse)) {
                throw new Exception('Invalid response: not an array');
            }

            if ($registrationResponse['status'] !== 200) {
                throw new Exception('Registration failed with status: ' . $registrationResponse['status']);
            }

            $body = $registrationResponse['body'];

            if (!isset($body['resultCode']) || $body['resultCode'] !== '0') {
                throw new Exception('Registration failed: ' . ($body['resultDesc'] ?? 'Unknown error'));
            }

            return [
                'success' => true,
                'message' => $body['resultDesc'] ?? 'Registration successful'
            ];
        } catch (Exception $e) {
            Log::error('Registration Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    protected function sendRegistrationRequest($data, $token)
    {
        $url = $this->baseUrl . '/digitalSignatureFullJwtSandbox/1.0/registration/v1';

        $requestBody = [
            'param' => array_merge($data, [
                'systemId' => $this->systemId
            ])
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'x-Gateway-APIKey' => $this->apiKey,
                'Content-Type' => 'application/json'
            ])
            ->withOptions([
                'verify' => false,
                'timeout' => 30
            ])
            ->post($url, $requestBody);

            return [
                'status' => $response->status(),
                'body' => $response->json() ?? []
            ];
        } catch (Exception $e) {
            Log::error('Error in sendRegistrationRequest', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => 500,
                'body' => [
                    'resultCode' => 'ERROR',
                    'resultDesc' => $e->getMessage()
                ]
            ];
        }
    }

    public function sendSpecimen(array $data)
    {
        try {
            // Dapatkan token JWT
            $token = $this->getJwtToken();
            if (!$token) {
                throw new \Exception('Tidak dapat mendapatkan token JWT');
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'x-Gateway-APIKey' => $this->apiKey,
                'Content-Type' => 'application/json'
            ])
            ->withOptions([
                'verify' => false,
                'timeout' => 30
            ])
            ->post($this->baseUrl . '/digitalSignatureFullJwtSandbox/1.0/sendSpeciment/v1', [
                'param' => [
                    'email' => $data['email'],
                    'systemId' => $this->systemId,
                    'speciment' => $data['specimen']
                ]
            ]);

            $result = $response->json();
            Log::info('Send specimen response', ['response' => $result]);

            if ($response->successful() && isset($result['resultCode']) && $result['resultCode'] === '0') {
                return [
                    'success' => true,
                    'message' => $result['resultDesc'] ?? 'Specimen berhasil dikirim'
                ];
            }

            throw new \Exception($result['resultDesc'] ?? 'Gagal mengirim specimen');

        } catch (\Exception $e) {
            Log::error('Send specimen error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}