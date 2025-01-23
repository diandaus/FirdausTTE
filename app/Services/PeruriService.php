<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;

class PeruriService
{
    protected $baseUrl;
    protected $apiKey;
    protected $systemId;

    // Error code constants
    const ERROR_CODES = [
        '0' => 'Sukses',
        '1001' => 'Sistem ID tidak valid',
        '1002' => 'Alamat email sudah terdaftar',
        '1003' => 'Pelanggan sudah terdaftar',
        '1004' => 'NIK tidak valid',
        '1005' => 'CRM gagal',
        '1006' => 'Verifikasi KYC ditolak',
        '1007' => 'Alamat email tidak diijinkan',
        '1008' => 'Data mandatory tidak valid'
    ];

    public function __construct()
    {
        $this->baseUrl = config('peruri.base_url');
        $this->apiKey = config('peruri.api_key');
        $this->systemId = config('peruri.system_id');
    }

    protected function getJWT()
    {
        try {
            $baseUrl = rtrim($this->baseUrl, '/');
            $endpoint = $baseUrl . '/jwt/1.0/getJsonWebToken/v1';
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-Gateway-APIKey' => $this->apiKey
            ])->post($endpoint, [
                'param' => [
                    'systemId' => $this->systemId
                ]
            ]);

            if (!$response->successful()) {
                throw new Exception('Failed to get JWT: ' . $response->body());
            }

            $data = $response->json();
            
            if (!isset($data['data']['jwt'])) {
                throw new Exception('JWT token not found in response');
            }

            Log::info('JWT Token Generated', [
                'token_preview' => substr($data['data']['jwt'], 0, 30) . '...'
            ]);

            return $data['data']['jwt'];
            
        } catch (Exception $e) {
            Log::error('JWT Generation Error', [
                'message' => $e->getMessage(),
                'system_id' => $this->systemId,
                'endpoint' => $endpoint ?? null
            ]);
            throw new Exception('JWT Error: ' . $e->getMessage());
        }
    }

    public function register($data)
    {
        try {
            $jwt = $this->getJWT();
            
            $registrationData = $this->prepareRegistrationData($data);
            
            Log::info('Sending registration request to Peruri', [
                'data' => $this->maskSensitiveData($registrationData)
            ]);

            $response = $this->sendRegistrationRequest($jwt, $registrationData);
            
            Log::info('Peruri registration response', [
                'response' => $response->json()
            ]);

            return $this->handleResponse($response->json(), $data);

        } catch (Exception $e) {
            Log::error('Registration Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $data['email'] ?? null,
                'ktp' => isset($data['ktp']) ? $this->maskKTP($data['ktp']) : null
            ]);

            return [
                'success' => false,
                'message' => 'Registrasi gagal: ' . $e->getMessage()
            ];
        }
    }

    protected function prepareRegistrationData($data)
    {
        return [
            'param' => [
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'type' => $data['type'] ?? 'INDIVIDUAL',
                'ktp' => $data['ktp'],
                'ktpPhoto' => $data['ktpPhoto'] ?? '',
                'address' => $data['address'],
                'city' => $data['city'],
                'province' => $data['province'],
                'gender' => $data['gender'],
                'placeOfBirth' => $data['placeOfBirth'],
                'dateOfBirth' => Carbon::parse($data['dateOfBirth'])->format('d/m/Y'),
                'orgUnit' => $data['orgUnit'] ?? '',
                'workUnit' => $data['workUnit'] ?? '',
                'position' => $data['position'] ?? '',
                'systemId' => $this->systemId
            ]
        ];
    }

    protected function sendRegistrationRequest($jwt, $registrationData)
    {
        $baseUrl = rtrim($this->baseUrl, '/');
        $endpoint = $baseUrl . '/digitalSignatureRSIslamIbnuSinaSigli/1.0/registration/v1';

        Log::info('Registration Request Details', [
            'endpoint' => $endpoint,
            'headers' => [
                'x-Gateway-APIKey' => substr($this->apiKey, 0, 10) . '...',
                'Authorization' => 'Bearer ' . substr($jwt, 0, 30) . '...'
            ],
            'request_data' => $this->maskSensitiveData($registrationData)
        ]);

        return Http::timeout(60)
            ->withHeaders([
                'x-Gateway-APIKey' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $jwt
            ])
            ->withOptions([
                'debug' => false,
                'verify' => false
            ])
            ->post($endpoint, $registrationData);
    }

    protected function handleResponse($responseData, $data)
    {
        $resultCode = $responseData['resultCode'] ?? 'unknown';
        $errorMessage = self::ERROR_CODES[$resultCode] ?? 'Terjadi kesalahan yang tidak diketahui';

        switch ($resultCode) {
            case '0':
                return [
                    'success' => true,
                    'message' => 'Registrasi berhasil',
                    'data' => $responseData['data'] ?? $responseData
                ];
            case '1001':
            case '1004':
            case '1005':
            case '1006':
            case '1007':
            case '1008':
                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'code' => $resultCode
                ];
            case '1002':
                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'code' => $resultCode,
                    'email' => $data['email']
                ];
            case '1003':
                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'code' => $resultCode,
                    'ktp' => $this->maskKTP($data['ktp'])
                ];
            default:
                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'code' => $resultCode
                ];
        }
    }

    protected function handleEmailAlreadyRegistered($emailStatus, $email)
    {
        return [
            'success' => false,
            'message' => 'Email sudah terdaftar dengan NIK: ' . 
                        $this->maskKTP($emailStatus['data']['nik']),
            'code' => '1002',
            'details' => [
                'email' => $email,
                'registered_phone' => $this->maskPhone($emailStatus['data']['phone']),
                'is_expired' => $emailStatus['data']['isExpired']
            ]
        ];
    }

    protected function maskKTP($ktp)
    {
        return substr($ktp, 0, 6) . '****' . substr($ktp, -4);
    }

    protected function maskPhone($phone)
    {
        return substr($phone, 0, 4) . '****' . substr($phone, -4);
    }

    protected function maskSensitiveData($data)
    {
        return array_merge(
            $data,
            ['param' => array_merge(
                $data['param'],
                [
                    'ktpPhoto' => 'BASE64_CONTENT_HIDDEN',
                    'ktp' => $this->maskKTP($data['param']['ktp'])
                ]
            )]
        );
    }

    public function verifyVideo($data)
    {
        try {
            $jwt = $this->getJWT();
            
            Log::info('Starting video verification', [
                'email' => $data['email']
            ]);

            // Format request body persis seperti curl example
            $verificationData = [
                'param' => [
                    'email' => $data['email'],
                    'systemId' => $this->systemId,
                    'videoStream' => $data['videoStream']  // Pure base64 tanpa MIME
                ]
            ];

            $baseUrl = rtrim($this->baseUrl, '/');
            $endpoint = $baseUrl . '/digitalSignatureRSIslamIbnuSinaSigli/1.0/videoVerification/v1';

            Log::info('Video verification request', [
                'endpoint' => $endpoint,
                'request_data' => [
                    'email' => $data['email'],
                    'systemId' => $this->systemId,
                    'videoSize' => strlen($data['videoStream'])
                ]
            ]);

            // Headers sesuai dengan curl
            $response = Http::timeout(180)
                ->withHeaders([
                    'x-Gateway-APIKey' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $jwt
                ])
                ->post($endpoint, $verificationData);

            if (!$response->successful()) {
                Log::error('Video verification failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new Exception('Video verification failed: ' . $response->body());
            }

            $result = $response->json();
            
            Log::info('Video verification response', [
                'result' => $result
            ]);

            switch($result['resultCode']) {
                case '0':
                    return [
                        'success' => true,
                        'message' => 'Verifikasi video berhasil dan sertifikat sedang digenerate',
                        'status' => 'VERIFIED',
                        'data' => $result
                    ];
                case '1040':
                    return [
                        'success' => true,
                        'message' => 'Video sedang dalam proses verifikasi',
                        'status' => 'PENDING_VERIFICATION',
                        'data' => $result,
                        'continue' => true
                    ];
                case '1006':
                    return [
                        'success' => true,
                        'message' => 'Video akan diverifikasi secara manual',
                        'status' => 'PENDING_MANUAL_VERIFICATION',
                        'data' => $result,
                        'continue' => true
                    ];
                case '1045':
                    return [
                        'success' => false,
                        'message' => 'Format video tidak valid. Pastikan menggunakan format MP4 (H.264) atau WEBM.',
                        'data' => $result
                    ];
                default:
                    return [
                        'success' => false,
                        'message' => $result['resultDesc'] ?? 'Verifikasi video gagal',
                        'data' => $result
                    ];
            }

        } catch (Exception $e) {
            Log::error('Video verification error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal melakukan verifikasi video: ' . $e->getMessage()
            ];
        }
    }

    // Tambahkan method untuk cek status sertifikat
    public function checkCertificateStatus($email)
    {
        try {
            $jwt = $this->getJWT();
            
            Log::info('Checking certificate status', [
                'email' => $email
            ]);

            // Format request body persis seperti curl example
            $requestData = [
                'param' => [
                    'email' => $email,
                    'systemId' => $this->systemId
                ]
            ];

            $baseUrl = rtrim($this->baseUrl, '/');
            $endpoint = $baseUrl . '/digitalSignatureSession/1.0/checkCertificate/v1';

            Log::info('Certificate status check request', [
                'endpoint' => $endpoint,
                'request_data' => $requestData
            ]);

            // Headers sesuai dengan curl
            $response = Http::timeout(30)
                ->withHeaders([
                    'x-Gateway-APIKey' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $jwt
                ])
                ->post($endpoint, $requestData);

            if (!$response->successful()) {
                Log::error('Certificate status check failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new Exception('Failed to check certificate status: ' . $response->body());
            }

            $result = $response->json();
            
            Log::info('Certificate status check response', [
                'result' => $result
            ]);

            // Periksa status berdasarkan response
            if ($result['resultCode'] === '0') {
                return [
                    'success' => true,
                    'status' => 'COMPLETED',
                    'message' => 'Sertifikat aktif dan valid',
                    'data' => $result['data'] ?? []
                ];
            } else {
                return [
                    'success' => false,
                    'status' => 'PENDING',
                    'message' => $result['resultDesc'] ?? 'Sertifikat belum aktif atau tidak ditemukan',
                    'data' => $result
                ];
            }

        } catch (Exception $e) {
            Log::error('Certificate status check error', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memeriksa status sertifikat: ' . $e->getMessage(),
                'status' => 'ERROR'
            ];
        }
    }

    public function sendSpecimen($data)
    {
        try {
            $jwt = $this->getJWT();
            
            Log::info('Starting specimen submission', [
                'email' => $data['email']
            ]);

            // Format request body sesuai dengan curl example
            $specimenData = [
                'param' => [
                    'email' => $data['email'],
                    'systemId' => $this->systemId,
                    'speciment' => $data['specimen']  // Base64 specimen
                ]
            ];

            $baseUrl = rtrim($this->baseUrl, '/');
            $endpoint = $baseUrl . '/digitalSignatureSession/1.0/sendSpeciment/v1';

            Log::info('Sending specimen request', [
                'endpoint' => $endpoint,
                'email' => $data['email']
            ]);

            // Headers sesuai dengan curl
            $response = Http::timeout(60)
                ->withHeaders([
                    'x-Gateway-APIKey' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $jwt
                ])
                ->post($endpoint, $specimenData);

            if (!$response->successful()) {
                Log::error('Specimen submission failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new Exception('Specimen submission failed: ' . $response->body());
            }

            $result = $response->json();
            
            Log::info('Specimen submission response', [
                'result' => $result
            ]);

            // Handle response codes
            switch($result['resultCode']) {
                case '0':
                    return [
                        'success' => true,
                        'message' => 'Specimen berhasil dikirim',
                        'data' => $result
                    ];
                case '1006':
                    return [
                        'success' => true,
                        'message' => 'Specimen akan diverifikasi secara manual',
                        'status' => 'PENDING_VERIFICATION',
                        'data' => $result
                    ];
                default:
                    return [
                        'success' => false,
                        'message' => $result['resultDesc'] ?? 'Gagal mengirim specimen',
                        'data' => $result
                    ];
            }

        } catch (Exception $e) {
            Log::error('Specimen submission error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengirim specimen: ' . $e->getMessage()
            ];
        }
    }
}
