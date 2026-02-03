<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Pocket;

class GeminiService
{
    private const API_ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models';

    /**
     * Analisis kondisi keuangan HARI INI
     */
    public function analyzeFinance(
        $user,
        float $todayExpense,
        float $dailyBudget,
        float $mainBalance,
        int $remainingDays,
        $transactions
    ): string {
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            return $this->generateLocalRoasting($dailyBudget, $todayExpense);
        }

        try {
            $remainingToday = max(0, $dailyBudget - $todayExpense);

            $summary = <<<TEXT
Nama: {$user->name}
Saldo Utama: Rp {$mainBalance}
Sisa Hari: {$remainingDays}

Jatah: {$dailyBudget}
Keluar: {$todayExpense}
Sisa: {$remainingToday}
TEXT;

            $prompt = <<<PROMPT
Komentari kondisi keuangan HARI INI.
1 kalimat, max 15 kata, santai, sarkas lucu.

{$summary}
PROMPT;

            $response = Http::timeout(8)->post(
                self::API_ENDPOINT . '/gemini-2.5-flash-lite:generateContent?key=' . $apiKey,
                ['contents' => [['parts' => [['text' => $prompt]]]]]
            );

            if ($response->failed()) {
                return $this->generateLocalRoasting($dailyBudget, $todayExpense);
            }

            $text = $response->json('candidates.0.content.parts.0.text');

            return $text && strlen(trim($text)) > 3
                ? trim($text)
                : $this->generateLocalRoasting($dailyBudget, $todayExpense);

        } catch (\Throwable $e) {
            Log::error('[Gemini] analyzeFinance error', ['error' => $e->getMessage()]);
            return $this->generateLocalRoasting($dailyBudget, $todayExpense);
        }
    }

    private function generateLocalRoasting(float $dailyBudget, float $todayExpense): string
    {
        if ($todayExpense > $dailyBudget) {
            return 'Hari ini kebablasan, dompet kamu lagi nangis.';
        }

        if ($todayExpense >= $dailyBudget * 0.8) {
            return 'Hampir habis, tarik napas sebelum checkout.';
        }

        if ($todayExpense > 0) {
            return 'Masih aman, jangan mulai khilaf.';
        }

        return 'Belum jajan, dompet masih senyum.';
    }

    /**
     * Extract transaksi dari teks natural
     */
    public function extractTransaction(string $inputText): array
    {
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            return $this->fallbackParse($inputText);
        }

        try {
            $categories = Category::whereNull('user_id')
                ->orWhere('user_id', Auth::id())
                ->get()
                ->map(fn ($c) => "ID {$c->id}: {$c->name}")
                ->join(', ');

            $pockets = Pocket::where('user_id', Auth::id())
                ->get()
                ->map(fn ($p) => "ID {$p->id}: {$p->name}")
                ->join(', ');

            $prompt = <<<PROMPT
Ekstrak JSON dari teks: "{$inputText}"

Kategori: {$categories}
Pocket: {$pockets}

Format:
{
  "amount": 15000,
  "category_id": 1,
  "pocket_id": null,
  "description": "Makan Ayam"
}
PROMPT;

            $response = Http::timeout(10)->post(
                self::API_ENDPOINT . '/gemini-2.5-flash-lite:generateContent?key=' . $apiKey,
                ['contents' => [['parts' => [['text' => $prompt]]]]]
            );

            if ($response->failed()) {
                return $this->fallbackParse($inputText);
            }

            $text = $response->json('candidates.0.content.parts.0.text');

            if (preg_match('/\{.*\}/s', $text, $m)) {
                $json = json_decode($m[0], true);
                if (isset($json['amount']) && $json['amount'] > 0) {
                    return $json;
                }
            }

            return $this->fallbackParse($inputText);

        } catch (\Throwable $e) {
            return $this->fallbackParse($inputText);
        }
    }

    private function fallbackParse(string $text): array
    {
        preg_match('/(\d+)(rb|k|ribu)?/i', $text, $m);

        $amount = $m
            ? ((int) $m[1]) * (isset($m[2]) ? 1000 : 1)
            : 0;

        $desc = trim(preg_replace('/(\d+)(rb|k|ribu)?/i', '', $text));

        return [
            'amount' => $amount,
            'category_id' => null,
            'pocket_id' => null,
            'description' => ucwords($desc) ?: 'Pengeluaran',
        ];
    }
}
