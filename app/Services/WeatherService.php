<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WeatherService
{
    /**
     * Get forecast for given lat/lon and date (Y-m-d or parseable date string).
     * Returns array with keys: temp, temp_min, temp_max, precip_mm, pop, weather, raw
     */
    public function getForecast(float $lat, float $lng, string $date)
    {
        try {
            $date = Carbon::parse($date)->format('Y-m-d');

            $params = [
                'latitude' => $lat,
                'longitude' => $lng,
                // daily variables we want
                'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_sum,precipitation_probability_max,weathercode',
                // up to 16 days supported by Open-Meteo
                'forecast_days' => 16,
                // return times in the local timezone for the location
                'timezone' => 'auto',
            ];

            $resp = Http::timeout(10)->get('https://api.open-meteo.com/v1/forecast', $params);

            if (!$resp->successful()) {
                Log::warning('Open-Meteo returned non-200', ['status' => $resp->status(), 'body' => $resp->body()]);
                return $this->fallback();
            }

            $json = $resp->json();
            $times = $json['daily']['time'] ?? [];

            if (!$times || !is_array($times)) {
                Log::warning('Open-Meteo missing daily.time', ['body' => $json]);
                return $this->fallback();
            }

            $index = array_search($date, $times);
            if ($index === false) {
                // date not in forecast range
                Log::info('Requested date not in forecast range', ['requested' => $date, 'available' => $times]);
                return $this->fallback();
            }

            $tmax = $json['daily']['temperature_2m_max'][$index] ?? null;
            $tmin = $json['daily']['temperature_2m_min'][$index] ?? null;
            $precip = $json['daily']['precipitation_sum'][$index] ?? 0;
            $pop = $json['daily']['precipitation_probability_max'][$index] ?? null;
            $wcode = $json['daily']['weathercode'][$index] ?? null;

            return [
                'temp' => $tmax !== null && $tmin !== null ? round((($tmax + $tmin) / 2), 1) : ($tmax ?? $tmin),
                'temp_min' => $tmin,
                'temp_max' => $tmax,
                'precip_mm' => $precip,
                'pop' => $pop, // precipitation probability (0-100)
                'weather' => $this->mapWeatherCode($wcode),
                // 'raw' => $json['daily'] ?? $json,
            ];
        } catch (\Exception $e) {
            Log::error('Open-Meteo API failed', ['message' => $e->getMessage()]);
            return $this->fallback();
        }
    }

    protected function fallback()
    {
        return [
            'temp' => 25,
            'temp_min' => null,
            'temp_max' => null,
            'precip_mm' => 0,
            'pop' => 0,
            'weather' => 'clear sky',
        ];
    }

    /**
     * Map Open-Meteo/WMO weather codes to short descriptions.
     * This list covers common codes — expand if you need more detail.
     */
    protected function mapWeatherCode($code)
    {
        if ($code === null) {
            return 'Unknown';
        }

        $map = [
            0  => 'Clear sky',
            1  => 'Mainly clear',
            2  => 'Partly cloudy',
            3  => 'Overcast',
            45 => 'Fog',
            48 => 'Depositing rime fog',
            51 => 'Light drizzle',
            53 => 'Moderate drizzle',
            55 => 'Dense drizzle',
            56 => 'Freezing drizzle',
            57 => 'Dense freezing drizzle',
            61 => 'Slight rain',
            63 => 'Moderate rain',
            65 => 'Heavy rain',
            66 => 'Freezing rain',
            67 => 'Heavy freezing rain',
            71 => 'Slight snow fall',
            73 => 'Moderate snow fall',
            75 => 'Heavy snow fall',
            77 => 'Snow grains',
            80 => 'Slight rain showers',
            81 => 'Moderate rain showers',
            82 => 'Violent rain showers',
            85 => 'Slight snow showers',
            86 => 'Heavy snow showers',
            95 => 'Thunderstorm',
            96 => 'Thunderstorm with slight hail',
            99 => 'Thunderstorm with heavy hail',
        ];

        return $map[$code] ?? 'Weather code '.$code;
    }
}
