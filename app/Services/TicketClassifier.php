<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Arr;

class TicketClassifier
{
    /** @return array{category:string,explanation:string,confidence:float} */
    public function classify(Ticket $t): array
    {
        // Toggle from config or env
        $enabled = filter_var(config('services.openai.classify_enabled', false), FILTER_VALIDATE_BOOL)
            || strcasecmp(env('OPENAI_CLASSIFY_ENABLED', 'false'), 'true') === 0;

        if (! $enabled) {
            // DEV fallback: random but valid
            $cats = ['Billing','Technical','Account','Other'];
            $cat  = $cats[array_rand($cats)];
            return [
                'category'    => $cat,
                'explanation' => "Heuristic/dev guess for '{$t->subject}'",
                'confidence'  => round(mt_rand(50, 95) / 100, 2), // 0.50–0.95
            ];
        }

        // --- Real OpenAI call goes here (stubbed for now) ---
        // Use a strict JSON-only system prompt, then json_decode.
        // $response = OpenAI::chat()->create([...]);
        // $raw = $response->choices[0]->message->content ?? '{}';

        $prompt = <<<SYS
You are a ticket classifier. Return ONLY valid JSON:
{ "category": "...", "explanation": "...", "confidence": 0.00 }
Categories: ["Billing","Technical","Account","Other"].
Confidence must be between 0 and 1 with two decimals.
SYS;

        // TEMP stub until you wire client:
        $raw = '{"category":"Technical","explanation":"Mentions server error","confidence":0.88}';

        $data = json_decode($raw, true) ?: [];
        $cat  = Arr::get($data, 'category', 'Other');
        $exp  = Arr::get($data, 'explanation', 'N/A');
        $conf = (float) Arr::get($data, 'confidence', 0.0);
        $conf = max(0.0, min(1.0, round($conf, 2)));

        if (! in_array($cat, ['Billing','Technical','Account','Other'], true)) {
            $cat = 'Other';
        }

        return ['category' => $cat, 'explanation' => $exp, 'confidence' => $conf];
    }
}
