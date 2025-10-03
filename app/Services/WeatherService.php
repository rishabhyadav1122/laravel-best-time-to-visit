<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class WeatherService
{
    public function getForecast(float $lat, float $lng, string $date)
    {
        try {
            $response = Http::get('http://api.weatherstack.com/forecast', [
                'access_key' => env('WEATHERSTACK_API_KEY'),
                'query' => "{$lat},{$lng}", // Use lat,lng directly
                'forecast_days' => 7,      // Get up to 7 days forecast
            ])->json();

            // Weatherstack returns forecast keyed by date
            foreach ($response['forecast'] ?? [] as $dayDate => $dayData) {
                if ($dayDate === $date) {
                    return [
                        'temp' => $dayData['avgtemp'] ?? null,
                        'weather' => $dayData['condition']['text'] ?? null,
                        'pop' => $dayData['precip'] ?? 0,
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::error('Weatherstack API failed', ['message' => $e->getMessage()]);
        }

        // Fallback if API fails
        return [
            'temp' => 25,
            'weather' => 'clear sky',
            'pop' => 0,
        ];
    }
}