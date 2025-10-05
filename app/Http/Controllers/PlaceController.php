<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleMapsService;
use App\Services\WeatherService;
use App\Services\GeminiService;

class PlaceController extends Controller
{
    protected $maps;
    protected $weather;
    protected $gemini;

    public function __construct(GoogleMapsService $maps, WeatherService $weather, GeminiService $gemini)
    {
        $this->maps = $maps;
        $this->weather = $weather;
        $this->gemini = $gemini;
    }

    /**
     * Show form (GET) or handle suggestion request (POST).
     */
    public function suggest(Request $request)
    {
        // If GET, show the form
        if (!$request->isMethod('post')) {
            return view('place.suggest');
        }

        // Validate inputs
        $validated = $request->validate([
            'place' => 'required|string',
            'date'  => 'required|date',
        ]);

        $placeName = $validated['place'];
        $date = $validated['date'];

        // debug object for developer inspection
        $debug = [
            'input' => [
                'place' => $placeName,
                'date' => $date,
            ],
        ];

        // 1) Find place
        $place = $this->maps->findPlace($placeName);
        if (!$place) {
            // If place not found, return view with an error message
            $error = 'Place not found. Please check the place name and try again.';
            return view('place.suggest', [
                'error' => $error,
                'old_place' => $placeName,
                'old_date' => $date,
                'debug' => $debug,
            ]);
        }

        // 2) Get details (may include types, opening_hours, reviews, etc.)
        $details = $this->maps->getPlaceDetails($place['place_id'] ?? null);
        $lat = $place['geometry']['location']['lat'] ?? null;
        $lng = $place['geometry']['location']['lng'] ?? null;

        $debug['place'] = [
            'basic' => $place,
            'details' => $details,
            'lat' => $lat,
            'lng' => $lng,
        ];

        // 3) Weather forecast (if lat/lng available)
        $weather = null;
        if ($lat && $lng) {
            $weather = $this->weather->getForecast($lat, $lng, $date);
        }
        $debug['weather'] = $weather;

        // 4) Build prompt for Gemini
        $types = implode(',', $details['types'] ?? []);
        $opening = $details['opening_hours'] ?? [];
        $reviews = $details['reviews'] ?? [];

        $prompt = "Suggest the best time of day to visit " . ($details['name'] ?? $placeName) . " on {$date}.\n";
        $prompt .= "Place type: {$types}\n";
        $prompt .= "Opening hours: " . json_encode($opening) . "\n";
        $prompt .= "Weather forecast: " . json_encode($weather) . "\n";
        $prompt .= "Recent reviews:\n";

        foreach (array_slice($reviews, 0, 3) as $review) {
            $prompt .= ($review['text'] ?? '') . "\n";
        }

        $prompt .= "Return JSON in this exact format:\n";
        $prompt .= "{\n";
        $prompt .= "  \"recommendation\": \"Best specific time of the day to visit (e.g., Sunrise, Morning 8–10AM, Evening 5–7PM)\",\n";
        $prompt .= "  \"reason\": [\n";
        $prompt .= "     \"Short, clear point 1\",\n";
        $prompt .= "     \"Short, clear point 2\",\n";
        $prompt .= "     \"Short, clear point 3\"\n";
        $prompt .= "  ],\n";
        $prompt .= "  \"confidence\": 0-100\n";
        $prompt .= "}\n";

        $debug['ai_prompt'] = $prompt;

        // 5) Call Gemini / AI service
        $aiResult = $this->gemini->getBestTime($prompt);
        $debug['ai_raw_result'] = $aiResult ?: 'No response';

        // 6) Normalize AI result into an array with expected keys
        $suggestion = [
            'recommendation' => 'No recommendation found',
            'reason' => '',
            'confidence' => 0,
        ];

        if ($aiResult) {
            // If the service returned an array already
            if (is_array($aiResult)) {
                $suggestion = array_merge($suggestion, $aiResult);
            } else {
                // If string, try decode JSON
                $decoded = json_decode($aiResult, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $suggestion = array_merge($suggestion, $decoded);
                } else {
                    // Try to extract a JSON object from somewhere in the string
                    if (preg_match('/\{.*\}/s', $aiResult, $m)) {
                        $decoded2 = json_decode($m[0], true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded2)) {
                            $suggestion = array_merge($suggestion, $decoded2);
                        } else {
                            // fallback: put raw text in recommendation
                            $suggestion['recommendation'] = trim($aiResult);
                        }
                    } else {
                        // fallback: put raw text in recommendation
                        $suggestion['recommendation'] = trim($aiResult);
                    }
                }
            }
        }

        // Ensure confidence is a number 0-100
        $confidence = $suggestion['confidence'] ?? 0;
        if (!is_numeric($confidence)) {
            // try to extract digits
            if (preg_match('/\d+/', (string)$confidence, $m)) {
                $confidence = (int)$m[0];
            } else {
                $confidence = 0;
            }
        }
        $suggestion['confidence'] = max(0, min(100, (int)$confidence));

        // Add debug suggestion
        $debug['parsed_suggestion'] = $suggestion;

        // 7) Build final enriched recommendation for view
        $formattedWeather = null;
        if (!empty($weather)) {
            $formattedWeather = sprintf(
                "🌤 %s — Avg: %s°C (Min: %s°C, Max: %s°C)",
                ucfirst($weather['weather'] ?? 'Unknown'),
                $weather['temp'] ?? '–',
                $weather['temp_min'] ?? '–',
                $weather['temp_max'] ?? '–',
            );
        }

        // Ensure a clean, readable recommendation
        $recommendationText = trim($suggestion['recommendation'] ?? '');
        if ($recommendationText && !preg_match('/\bam\b|\bpm\b|\d/', $recommendationText)) {
            $recommendationText = 'Best time to visit: ' . $recommendationText;
        }

        $viewData = [
            'suggestion' => [
                'recommendation' => $recommendationText,
                'reason' => $suggestion['reason'] ?? [],
                'confidence' => $suggestion['confidence'] ?? 0,
                'weather_summary' => $formattedWeather,
            ],
            'place' => $details['name'] ?? $placeName,
            'date' => $date,
            'old_place' => $placeName,
            'old_date' => $date,
        ];

        // Attach debug only if app debug is enabled (safe practice)
        if (config('app.debug')) {
            $viewData['debug'] = $debug;
        }

        return view('place.suggest', $viewData);
    }
}
