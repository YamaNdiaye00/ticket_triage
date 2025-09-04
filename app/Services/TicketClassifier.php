<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;
use Throwable;

class TicketClassifier
{
    /** @return array{category:string,explanation:string,confidence:float} */
    public function classify(Ticket $t): array
    {
        // Feature flag: use ONLY config('openai.classify_enabled')
        $enabled = filter_var(config('openai.classify_enabled', false), FILTER_VALIDATE_BOOL);

        // DEV fallback helper
        $fallback = function () use ($t): array {
            $cats = ['Billing', 'Technical', 'Account', 'Other'];
            $cat = $cats[array_rand($cats)];
            return [
                'category' => $cat,
                'explanation' => "Heuristic/dev guess for '{$t->subject}'",
                'confidence' => round(mt_rand(50, 95) / 100, 2), // 0.50–0.95
            ];
        };

        if (!$enabled) {
            Log::info('TicketClassifier: using fallback (flag disabled)', ['ticket' => $t->id]);
            return $fallback();
        }


        // Ensure API key exists (prefer config(openai.*))
        $apiKey = (string)(config('openai.api_key') ?: env('OPENAI_API_KEY'));

        if ($apiKey === '') {
            Log::warning('TicketClassifier: no OPENAI_API_KEY in config/openai.php or env; using fallback', [
                'ticket' => $t->id,
            ]);
            return $fallback();
        }

        try {
            $system = <<<'SYS'
                You are a ticket classifier. Return ONLY valid JSON with EXACT keys:
                { "category": "...", "explanation": "...", "confidence": 0.00 }
                Allowed categories: ["Billing","Technical","Account","Other"] ONLY.
                "confidence" must be between 0 and 1 with two decimals.
                No extra text, no backticks, no code fences, no markdown. JSON only.
                SYS;

            // Only pass what's needed
            $user = json_encode([
                'subject' => $t->subject,
                'body' => $t->body,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            // Model from openai.php
            $model = (string)config('openai.model', 'gpt-4o-mini');

//            Log::info('TicketClassifier: calling OpenAI', [
//                'ticket' => $t->id,
//                'model' => $model,
//            ]);

            // Call Chat Completions API via facade
            $response = OpenAI::chat()->create([
                'model' => $model,
//                'max_tokens'  => 200,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $user],
                ],
            ]);

            $raw = $response->choices[0]->message->content ?? '{}';
//            Log::info('TicketClassifier: OpenAI raw', ['ticket' => $t->id, 'raw' => $raw]);

            // Extract JSON object
            $json = $this->extractJsonObject($raw);
            $data = json_decode($json, true) ?: [];

            // Safe reads
            $cat = Arr::get($data, 'category', 'Other');
            $exp = (string)Arr::get($data, 'explanation', 'N/A');
            $conf = (float)Arr::get($data, 'confidence', 0.0);

            $allowed = ['Billing', 'Technical', 'Account', 'Other'];
            if (!in_array($cat, $allowed, true)) {
                $cat = 'Other';
            }
            $conf = max(0.0, min(1.0, round($conf, 2)));

            return ['category' => $cat, 'explanation' => $exp, 'confidence' => $conf];
        } catch (Throwable $e) {
            Log::warning('TicketClassifier: OpenAI failed, using fallback', [
                'ticket' => $t->id,
                'msg' => $e->getMessage(),
            ]);
            return $fallback();
        }
    }

    /** Grab the first {...} block if fences/extra text sneak in */
    private function extractJsonObject(string $raw): string
    {
        // fast path
        if (str_starts_with(trim($raw), '{')) return $raw;

        if (preg_match('/\{.*\}/s', $raw, $m)) {
            return $m[0];
        }
        return '{}';
    }

}
