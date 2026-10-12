<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    /**
     * Daftar kecamatan di Majalengka (fallback jika API gagal)
     */
    protected array $kecamatanList = [
        'Cigasong', 'Majalengka', 'Kadipaten', 'Ligung', 
        'Sumberjaya', 'Jatiwangi', 'Dawuan', 'Sindang',
        'Rajagaluh', 'Panyingkiran', 'Sindangwangi', 'Leuwimunding',
        'Palasah', 'Bantarujeg', 'Sukahaji', 'Malausma',
        'Kertajati', 'Argapura', 'Banjaranyar', 'Cikijing'
    ];

    /**
     * Reverse geocoding: koordinat -> kecamatan via Nominatim
     */
    public function detectKecamatan(float $lat, float $lng): array
    {
        $cacheKey = "kecamatan_{$lat}_{$lng}";

        // Cache selama 24 jam untuk mengurangi API calls
        return Cache::remember($cacheKey, 86400, function () use ($lat, $lng) {
            try {
                $response = Http::timeout(10)
                    ->withHeaders([
                        'User-Agent' => 'WasteTripApp/1.0 (your-email@example.com)'
                    ])
                    ->get('https://nominatim.openstreetmap.org/reverse', [
                        'lat' => $lat,
                        'lon' => $lng,
                        'format' => 'json',
                        'addressdetails' => 1,
                        'accept-language' => 'id'
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $address = $data['address'] ?? [];

                    // Coba ambil subdistrict (kecamatan) atau county
                    $detected = $address['subdistrict'] 
                        ?? $address['county'] 
                        ?? $address['city_district']
                        ?? null;

                    if ($detected) {
                        // Bersihkan nama kecamatan
                        $detected = $this->cleanKecamatanName($detected);

                        return [
                            'success' => true,
                            'detected' => $detected,
                            'full_address' => $data['display_name'] ?? null,
                            'source' => 'api',
                            'options' => $this->buildOptions($detected)
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Nominatim API failed: ' . $e->getMessage());
            }

            // Fallback: kembalikan daftar manual
            return [
                'success' => false,
                'detected' => null,
                'full_address' => null,
                'source' => 'fallback',
                'options' => $this->buildOptions(null)
            ];
        });
    }

    /**
     * Bersihkan nama kecamatan dari suffix/prefix
     */
    protected function cleanKecamatanName(string $name): string
    {
        $name = trim($name);

        // Hapus "Kecamatan" prefix
        $name = preg_replace('/^kecamatan\s+/i', '', $name);

        // Hapus "Kabupaten" suffix
        $name = preg_replace('/\s+kabupate[n]?$/i', '', $name);

        return ucfirst(strtolower($name));
    }

    /**
     * Build dropdown options dengan detected value di atas
     */
    protected function buildOptions(?string $detected): array
    {
        $options = [];

        if ($detected && in_array($detected, $this->kecamatanList)) {
            $options[] = [
                'value' => $detected,
                'label' => $detected . ' (terdeteksi)',
                'selected' => true
            ];
        }

        foreach ($this->kecamatanList as $kec) {
            if ($kec !== $detected) {
                $options[] = [
                    'value' => $kec,
                    'label' => $kec,
                    'selected' => false
                ];
            }
        }

        return $options;
    }

    /**
     * Get semua kecamatan untuk dropdown manual
     */
    public function getKecamatanList(): array
    {
        return $this->kecamatanList;
    }

    /**
     * Batch detect untuk multiple coordinates (untuk pickup points)
     */
    public function detectBatch(array $coordinates): array
    {
        $results = [];
        foreach ($coordinates as $index => $coord) {
            $results[$index] = $this->detectKecamatan($coord['lat'], $coord['lng']);
        }
        return $results;
    }
}