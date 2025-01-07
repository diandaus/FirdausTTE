<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Intervention\Image\ImageManagerStatic as Image;

class OCRController extends Controller
{
    public function processKTP(Request $request)
    {
        try {
            if (!$this->isTesseractInstalled()) {
                throw new \Exception('Tesseract OCR tidak terinstall di server');
            }

            $request->validate([
                'ktpPhoto' => 'required|image|max:2048'
            ]);

            $path = $request->file('ktpPhoto')->store('temp', 'public');
            $fullPath = storage_path('app/public/' . $path);

            // Enhanced image preprocessing
            $image = Image::make($fullPath);
            
            $image->resize(2000, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $image->greyscale();
            $image->brightness(10)->contrast(20)->gamma(1.2);
            $image->sharpen(25);
            $image->blur(0.5);
            $image->contrast(15);

            $processedPath = storage_path('app/public/temp/processed_' . basename($path));
            $image->save($processedPath, 100);

            try {
                $ocr = new TesseractOCR($processedPath);
                $allowedChars = array_merge(
                    range('A', 'Z'),
                    range('a', 'z'),
                    range('0', '9'),
                    ['-', '/', '.', ':', ' ', ',']
                );

                $text = $ocr->lang('ind')
                    ->psm(3)
                    ->oem(1)
                    ->dpi(300)
                    ->allowlist(implode('', $allowedChars))
                    ->configFile('digits')
                    ->run();

                Log::info('Raw OCR Text', ['text' => $text]);

                if (empty(trim($text)) || preg_match('/^[\s\-\.]+$/', $text)) {
                    throw new \Exception('Tidak dapat membaca text dari gambar');
                }

                $text = $this->preprocessOCRText($text);
                Log::info('Preprocessed Text', ['text' => $text]);

                $data = $this->parseKTPTextAdvanced($text);
                Log::info('Parsed Data', ['data' => $data]);

                if (empty($data['nik'])) {
                    throw new \Exception('NIK tidak dapat dibaca dari KTP');
                }

                $gender = '';
                if (!empty($data['gender'])) {
                    $gender = strtoupper($data['gender']);
                    if (strpos($gender, 'LAKI') !== false) {
                        $gender = 'M';
                    } elseif (strpos($gender, 'PEREMP') !== false) {
                        $gender = 'F';
                    }
                }

                $birthDate = '';
                if (!empty($data['birthDate'])) {
                    try {
                        $birthDate = \Carbon\Carbon::parse($data['birthDate'])->format('Y-m-d');
                    } catch (\Exception $e) {
                        Log::warning('Invalid birth date format', ['date' => $data['birthDate']]);
                    }
                }

                return response()->json([
                    'success' => true,
                    'result' => [
                        'imageUrl' => asset('storage/' . $path),
                        'name' => $data['name'] ?? '',
                        'nik' => $data['nik'] ?? '',
                        'birthPlace' => $data['birthPlace'] ?? '',
                        'birthDate' => $birthDate,
                        'gender' => $gender,
                        'address' => $data['address'] ?? '',
                    ]
                ]);

            } catch (\Exception $e) {
                Log::error('OCR Processing Error', [
                    'message' => $e->getMessage(),
                    'raw_text' => $text ?? null,
                    'processed_path' => $processedPath
                ]);
                throw new \Exception('Gagal membaca KTP. Pastikan foto jelas dan tidak blur.');
            }

        } catch (\Exception $e) {
            Log::error('OCR Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if (isset($processedPath)) @unlink($processedPath);
            if (isset($fullPath)) @unlink($fullPath);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function isTesseractInstalled()
    {
        exec('which tesseract', $output, $returnVar);
        return $returnVar === 0;
    }

    private function preprocessOCRText($text) 
    {
        $text = preg_replace('/[^\w\s\-\/\:]/', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        return $text;
    }

    private function parseKTPTextAdvanced($text)
    {
        $data = [];
        
        if (preg_match('/\b\d{16}\b/', $text, $matches)) {
            $data['nik'] = $matches[0];
        }

        $namePatterns = [
            '/Nama\s*:?\s*([^:]+?)(?=\s*(?:Tempat|Lahir|NIK|Jenis|Kelamin|Alamat|Gol|Darah|Status|Pekerjaan))/i',
            '/\b([A-Z][A-Z\s\.]+[A-Z])\b(?=\s*(?:Tempat|Lahir|NIK|Jenis|Kelamin|Alamat))/i',
            '/\b\d{16}\b\s*([^0-9]+?)(?=\s*(?:Tempat|Lahir))/i',
            '/^([^0-9]+?)(?=\s*(?:Tempat|Lahir))/i'
        ];

        foreach ($namePatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $name = trim($matches[1]);
                if (strlen($name) > 3 && 
                    !preg_match('/(tempat|lahir|nik|jenis|kelamin|alamat|provinsi|kota|kabupaten|kecamatan|kelurahan|agama|status|pekerjaan|kewarganegaraan|berlaku|gol|darah)/i', $name)) {
                    $data['name'] = $name;
                    break;
                }
            }
        }

        if (preg_match('/Tempat(?:\/Tgl)?\.?\s*Lahir\s*:?\s*([^,\n]+?)(?=\s*,\s*\d{2}[-\/]\d{2}[-\/]\d{4})/i', $text, $matches)) {
            $data['birthPlace'] = trim($matches[1]);
        }

        if (preg_match('/\b(\d{2})[-\/](\d{2})[-\/](\d{4})\b/', $text, $matches)) {
            $data['birthDate'] = sprintf('%s-%s-%s', $matches[3], $matches[2], $matches[1]);
        }

        if (preg_match('/(?:Jenis\s*Kelamin|JK)\s*:?\s*(LAKI[- ]*LAKI|PEREMPUAN)/i', $text, $matches)) {
            $data['gender'] = strtoupper(trim($matches[1]));
        }

        if (preg_match('/Alamat\s*:?\s*([^\.]+?)(?=\s*(?:RT|RW|Kel|Kec|Kab|Provinsi|NIK))/i', $text, $matches)) {
            $data['address'] = trim($matches[1]);
        }

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $value = preg_replace('/\s+/', ' ', $value);
                $value = preg_replace('/[^\w\s\-\.]/', '', $value);
                $data[$key] = ucwords(strtolower(trim($value)));
            }
        }

        if (isset($data['name'])) {
            $data['name'] = str_replace(['Nama', 'Lahir', 'Tempat', 'Jenis', 'Kelamin'], '', $data['name']);
            $data['name'] = trim($data['name']);
            if (strlen($data['name']) < 3) {
                unset($data['name']);
            }
        }

        Log::info('Parsed KTP Data', ['data' => $data]);

        return $data;
    }
}
