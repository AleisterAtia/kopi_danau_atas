<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper over the Google AI Studio (Gemini) REST API used to translate
 * admin CMS content from Indonesian to English. No SDK — just the HTTP client.
 *
 * Deliberately fail-soft: any error returns null so the caller keeps the
 * source text and the site falls back to Indonesian instead of breaking a save.
 */
class GeminiTranslator
{
    private const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';

    public function enabled(): bool
    {
        return filled(config('services.gemini.key'));
    }

    /**
     * Translate a map of field => text in a single request, preserving HTML.
     *
     * @param  array<string, string>  $fields  e.g. ['title' => '...', 'content' => '<p>...</p>']
     * @return array<string, string>|null  Same keys with translated values, or null on failure.
     */
    public function translateFields(array $fields, string $from = 'Indonesian', string $to = 'English'): ?array
    {
        $fields = array_filter($fields, fn ($v) => is_string($v) && trim($v) !== '');

        if (! $this->enabled() || $fields === []) {
            return null;
        }

        $prompt = <<<PROMPT
            You are a professional translator for a coffee agrotourism website.
            Translate the VALUES of the JSON object below from {$from} to {$to}.
            Rules:
            - Preserve all HTML tags and structure exactly; translate only the visible text.
            - Keep JSON keys unchanged.
            - Keep proper nouns and brand names natural (e.g. "CV Kopi Danau Atas",
              "Danau Diatas", "Alahan Panjang", "Solok"); translate "Arabika" as "Arabica".
            - Return ONLY the JSON object, nothing else.

            Input:
            {json}
            PROMPT;

        $prompt = str_replace('{json}', json_encode($fields, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $prompt);

        try {
            $response = Http::timeout(30)
                ->retry(2, 500, throw: false)
                ->post(
                    sprintf(self::ENDPOINT, config('services.gemini.model')).'?key='.config('services.gemini.key'),
                    [
                        'contents' => [['parts' => [['text' => $prompt]]]],
                        'generationConfig' => [
                            'temperature' => 0.2,
                            'responseMimeType' => 'application/json',
                        ],
                    ]
                );

            if ($response->failed()) {
                Log::warning('Gemini translate failed', ['status' => $response->status(), 'body' => $response->body()]);

                return null;
            }

            $text = data_get($response->json(), 'candidates.0.content.parts.0.text');
            $translated = json_decode((string) $text, true);

            if (! is_array($translated)) {
                Log::warning('Gemini returned non-JSON', ['text' => $text]);

                return null;
            }

            // Only keep keys we asked for, coerced to string.
            return array_map('strval', array_intersect_key($translated, $fields));
        } catch (\Throwable $e) {
            Log::warning('Gemini translate exception: '.$e->getMessage());

            return null;
        }
    }
}
