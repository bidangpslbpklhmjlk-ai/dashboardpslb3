<?php

namespace App\Console\Commands;

use App\Models\WasteTrip;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdateRouteGeometry extends Command
{
    protected $signature = 'waste-trip:update-routes';
    protected $description = 'Update route geometry untuk data lama';

    public function handle()
    {
        $trips = WasteTrip::whereNull('route_geometry')->get();
        
        $this->info("Found {$trips->count()} trips without route geometry");
        
        foreach ($trips as $trip) {
            $waypoints = [
                [(float)$trip->start_lat, (float)$trip->start_lng]
            ];
            
            foreach ($trip->pickup_points as $point) {
                $waypoints[] = [(float)$point['lat'], (float)$point['lng']];
            }
            
            if ($trip->end_lat && $trip->end_lng) {
                $waypoints[] = [(float)$trip->end_lat, (float)$trip->end_lng];
            }
            
            $coords = collect($waypoints)->map(fn($p) => "{$p[1]},{$p[0]}")->join(';');
            $url = "https://router.project-osrm.org/route/v1/driving/{$coords}?overview=full&geometries=geojson";
            
            try {
                $response = Http::timeout(10)->get($url);
                $data = $response->json();
                
                if (($data['code'] ?? '') === 'Ok' && !empty($data['routes'])) {
                    $route = $data['routes'][0];
                    
                    $geometry = collect($route['geometry']['coordinates'])
                        ->map(fn($coord) => [(float)$coord[1], (float)$coord[0]])
                        ->toArray();
                    
                    $trip->update([
                        'route_geometry' => $geometry,
                        'total_jarak' => round($route['distance'] / 1000, 2),
                    ]);
                    
                    $this->info("Updated: {$trip->nomor_polisi} - {$trip->tanggal}");
                }
            } catch (\Exception $e) {
                $this->error("Failed: {$trip->nomor_polisi} - {$e->getMessage()}");
            }
            
            // Delay untuk hindari rate limit
            sleep(1);
        }
        
        $this->info('Done!');
    }
}