<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    public function getBestTime(string $prompt)
    {
        $response = Http::post(
            "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . env('GEMINI_API_KEY'),
            [
                "contents" => [
                    ["parts" => [["text" => $prompt]]]
                ]
            ]
        );

        if ($response->failed()) {
            return 'Error: ' . $response->body();
        }

        $data = $response->json();

        return $data['candidates'][0]['content']['parts'][0]['text']
            ?? 'No response from Gemini';
    }
}
