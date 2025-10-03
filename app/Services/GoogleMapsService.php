<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GoogleMapsService
{
    public function findPlace(string $placeName)
    {
        $response = Http::get('https://maps.googleapis.com/maps/api/place/findplacefromtext/json', [
            'input' => $placeName,
            'inputtype' => 'textquery',
            'fields' => 'place_id,name,geometry,formatted_address,types',
            'key' => env('GOOGLE_MAPS_API_KEY'),
        ])->json();

        return $response['candidates'][0] ?? null;
    }

    public function getPlaceDetails(string $placeId)
    {
        $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
            'place_id' => $placeId,
            'fields' => 'name,formatted_address,opening_hours,types,reviews',
            'key' => env('GOOGLE_MAPS_API_KEY'),
        ])->json();

        return $response['result'] ?? null;
    }
}
