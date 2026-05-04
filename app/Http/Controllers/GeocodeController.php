<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeocodeController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['required', 'string', 'min:3', 'max:200'],
        ]);

        $response = Http::withHeaders([
            'User-Agent' => 'KPM-PPL-Manager/1.0 (Laravel)',
            'Accept-Language' => 'id',
        ])
            ->timeout(10)
            ->get('https://nominatim.openstreetmap.org/search', [
                'q' => $request->input('q'),
                'format' => 'json',
                'addressdetails' => 1,
                'limit' => 5,
                'countrycodes' => 'id',
            ]);

        if (! $response->successful()) {
            return response()->json(['error' => 'Geocoding service unavailable'], 502);
        }

        $results = collect($response->json())->map(fn ($r) => [
            'display_name' => $r['display_name'] ?? '',
            'lat' => (float) ($r['lat'] ?? 0),
            'lon' => (float) ($r['lon'] ?? 0),
        ]);

        return response()->json(['results' => $results]);
    }
}
