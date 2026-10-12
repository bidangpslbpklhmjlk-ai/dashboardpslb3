<?php

namespace App\Console\Commands;

use App\Models\WasteTrip;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class DetectKecamatanBatch extends Command
{
    protected $signature = 'waste-trip:detect-kecamatan 
                            {--force : Force update even if kecamatan already exists}
                            {--dry-run : Show what would be updated without saving}
                            {--limit= : Limit number of records to process}';
    
    protected $description = 'Auto-detect kecamatan untuk data lama via Nominatim API';

    /**
     * Daftar kecamatan Majalengka (fallback & validasi)
     */
    protected array $kecamatanList = [
        'Cigasong', 'Majalengka', 'Kadipaten', 'Ligung', 'Sumberjaya',
        'Jatiwangi', 'Dawuan', 'Sindang', 'Rajagaluh', 'Panyingkiran',
        'Sindangwangi', 'Leuwimunding', 'Palasah', 'Bantarujeg',
        'Sukahaji', 'Malausma', 'Kertajati', 'Argapura',
        'Banjaranyar', 'Cikijing'
    ];

    public function handle()
    {
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');
        $limit = $this->option('limit');

        // Build query
        $query = WasteTrip::query();
        
        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('start_kecamatan')
                  ->orWhereNull('end_kecamatan');
            });
        }
        
        if ($limit) {
            $query->limit((int) $limit);
        }

        $trips = $query->get();
        $total = $trips->count();
        
        if ($total === 0) {
            $this->info('✅ Tidak ada data yang perlu diproses.');
            return 0;
        }

        $this->info("🔍 Found {$total} trips to process");
        $this->newLine();

        $updated = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($trips as $trip) {
            $needsUpdate = false;
            $updates = [];

            // --- DETECT START KECAMATAN ---
            if ($force || empty($trip->start_kecamatan)) {
                if ($trip->start_lat && $trip->start_lng) {
                    $detected = $this->detectKecamatan((float)$trip->start_lat, (float)$trip->start_lng);
                    
                    if ($detected) {
                        $updates['start_kecamatan'] = $detected;
                        $needsUpdate = true;
                        
                        if ($dryRun) {
                            $this->line("  🟢 [DRY-RUN] Trip #{$trip->id} | Start: {$detected}");
                        }
                    } else {
                        $failed++;
                        if ($dryRun) {
                            $this->line("  🟡 [DRY-RUN] Trip #{$trip->id} | Start: FAILED");
                        }
                    }
                    
                    sleep(1); // Rate limit Nominatim
                }
            }

            // --- DETECT END KECAMATAN ---
            if ($force || empty($trip->end_kecamatan)) {
                if ($trip->end_lat && $trip->end_lng) {
                    $detected = $this->detectKecamatan((float)$trip->end_lat, (float)$trip->end_lng);
                    
                    if ($detected) {
                        $updates['end_kecamatan'] = $detected;
                        $needsUpdate = true;
                        
                        if ($dryRun) {
                            $this->line("  🟢 [DRY-RUN] Trip #{$trip->id} | End: {$detected}");
                        }
                    } else {
                        if (!$dryRun) $failed++;
                        if ($dryRun) {
                            $this->line("  🟡 [DRY-RUN] Trip #{$trip->id} | End: FAILED");
                        }
                    }
                    
                    sleep(1);
                }
            }

            // --- DETECT PICKUP POINTS KECAMATAN ---
            $pickupPoints = $trip->pickup_points ?? [];
            $pickupUpdated = false;
            
            foreach ($pickupPoints as $i => $point) {
                if ($force || empty($point['kecamatan'] ?? null)) {
                    if (($point['lat'] ?? null) && ($point['lng'] ?? null)) {
                        $detected = $this->detectKecamatan((float)$point['lat'], (float)$point['lng']);
                        
                        if ($detected) {
                            $pickupPoints[$i]['kecamatan'] = $detected;
                            $pickupUpdated = true;
                            
                            if ($dryRun) {
                                $this->line("  🟢 [DRY-RUN] Trip #{$trip->id} | Pickup #{$i}: {$detected}");
                            }
                        } else {
                            if (!$dryRun) $failed++;
                            if ($dryRun) {
                                $this->line("  🟡 [DRY-RUN] Trip #{$trip->id} | Pickup #{$i}: FAILED");
                            }
                        }
                        
                        sleep(1);
                    }
                }
            }
            
            if ($pickupUpdated) {
                $updates['pickup_points'] = $pickupPoints;
                $needsUpdate = true;
            }

            // --- SAVE ---
            if ($needsUpdate) {
                if (!$dryRun) {
                    $trip->update($updates);
                    $this->info("✅ Updated: {$trip->nomor_polisi} - {$trip->tanggal->format('d M Y')}");
                }
                $updated++;
            } else {
                $skipped++;
            }
        }

        $this->newLine();
        $this->info('📊 Summary:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Processed', $total],
                ['Updated', $updated],
                ['Skipped (already has)', $skipped],
                ['Failed Detect', $failed],
                ['Mode', $dryRun ? '🧪 DRY-RUN' : '💾 LIVE'],
            ]
        );

        $this->info('Done!');
        return 0;
    }

    /**
     * Detect kecamatan via Nominatim API
     */
    private function detectKecamatan(float $lat, float $lng): ?string
    {
        $cacheKey = "kecamatan_{$lat}_{$lng}";
        
        // Check cache first
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'WasteTripApp/1.0'
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
                
                $detected = $address['subdistrict'] 
                    ?? $address['county'] 
                    ?? $address['city_district']
                    ?? null;

                if ($detected) {
                    $detected = $this->cleanKecamatanName($detected);
                    
                    // Validate against known list
                    if (in_array($detected, $this->kecamatanList)) {
                        Cache::put($cacheKey, $detected, 86400); // Cache 24 hours
                        return $detected;
                    }
                    
                    // If not in list, try to find closest match
                    $closest = $this->findClosestMatch($detected);
                    if ($closest) {
                        Cache::put($cacheKey, $closest, 86400);
                        return $closest;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->warn("  API Error: {$e->getMessage()}");
        }

        return null;
    }

    /**
     * Clean kecamatan name
     */
    private function cleanKecamatanName(string $name): string
    {
        $name = trim($name);
        $name = preg_replace('/^kecamatan\s+/i', '', $name);
        $name = preg_replace('/\s+kabupate[n]?$/i', '', $name);
        return ucfirst(strtolower($name));
    }

    /**
     * Find closest matching kecamatan
     */
    private function findClosestMatch(string $detected): ?string
    {
        $detectedLower = strtolower($detected);
        
        foreach ($this->kecamatanList as $kec) {
            similar_text($detectedLower, strtolower($kec), $percent);
            if ($percent > 70) {
                return $kec;
            }
        }
        
        return null;
    }
}