<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Category;
use App\Models\Pocket;

class GeminiService
{
    private const API_ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models';

    /**
     * Analisis kondisi keuangan user dan generate roasting
     *
     * Fallback chain: Flash-Lite → Pro → Local Roasting
     *
     * @param \App\Models\User $user
     * @param \Illuminate\Database\Eloquent\Collection $transactions
     * @param float $dailyBudget
     * @param float $mainBalance
     * @param int $remainingDays
     * @return string Pesan roasting dari AI atau local logic
     */
    public function analyzeFinance($user, $transactions, $dailyBudget, $mainBalance, $remainingDays)
    {
        $apiKey = env('GEMINI_API_KEY');

        if (empty($apiKey)) {
            Log::warning('[Gemini] API Key Missing');
            return $this->generateLocalRoasting($dailyBudget, $user->name);
        }

        try {
            $dailyFormatted = number_format($dailyBudget, 0, ',', '.');

            // Format 3 transaksi terakhir untuk konteks
            $transactionList = $transactions->take(3)->map(fn($t) =>
                "- {$t->description}"
            )->join(", ");

            if (empty($transactionList)) {
                $transactionList = "Belum ada transaksi";
            }

            // Summary data untuk AI
            $summary = <<<TEXT
                            User: {$user->name}
                            Jatah Harian Hari Ini: Rp {$dailyFormatted}
                            Transaksi Terakhir: {$transactionList}
                        TEXT;

            // Prompt yang ketat untuk hasil konsisten
            $prompt = <<<PROMPT
                            Kamu adalah asisten keuangan yang SARKAS dan LUCU. Roasting Mode.
                            Tugasmu: Komentari JATAH HARIAN user dengan 1 kalimat pendek yang menghibur.

                            Data:
                            {$summary}

                            ATURAN MUTLAK:
                            1. HANYA 1 KALIMAT (max 15 kata).
                            2. Fokus ke jatah harian: apakah gede, dikit, atau wajar.
                            3. Sarkastis tapi peduli.
                            4. Bahasa Indonesia Gaul.

                            LOGIKA ROASTING:
                            - Jatah > 700rb: "Wah, lumayan lah jatahnya, jangan sampe ilang di coffee shop ya!"
                            - Jatah 300-700rb: "Cukupan, tapi hati-hati sama belanja online."
                            - Jatah < 300rb: "Aduh, jatahnya pas-pasan banget, harus pinter-pinter."
                        PROMPT;

            Log::info('[Gemini] Attempting analyzeFinance with gemini-2.5-flash-lite');

            // 1. Coba Flash-Lite (quota friendly)
            $response = Http::withoutVerifying()
                ->timeout(8)
                ->post(
                    self::API_ENDPOINT . '/gemini-2.5-flash-lite:generateContent?key=' . $apiKey,
                    [
                        'contents' => [['parts' => [['text' => $prompt]]]],
                        'generationConfig' => [
                            'temperature' => 0.9,
                            'maxOutputTokens' => 50
                        ]
                    ]
                );

            // 2. Fallback ke Pro jika Flash-Lite gagal
            if ($response->failed()) {
                Log::warning('[Gemini] Flash-Lite failed, attempting gemini-2.5-pro');

                $response = Http::withoutVerifying()
                    ->timeout(8)
                    ->post(
                        self::API_ENDPOINT . '/gemini-2.5-pro:generateContent?key=' . $apiKey,
                        [
                            'contents' => [['parts' => [['text' => $prompt]]]],
                            'generationConfig' => [
                                'temperature' => 0.9,
                                'maxOutputTokens' => 50
                            ]
                        ]
                    );
            }

            // 3. Jika kedua API gagal, gunakan local roasting
            if ($response->failed()) {
                Log::warning('[Gemini] Both API models failed, using local roasting', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return $this->generateLocalRoasting($dailyBudget, $user->name);
            }

            $result = $response->json();
            $message = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

            // Validasi response
            if (!$message || strlen(trim($message)) < 3) {
                Log::warning('[Gemini] Invalid response from API');
                return $this->generateLocalRoasting($dailyBudget, $user->name);
            }

            Log::info('[Gemini] Success: ' . trim($message));
            return trim($message);

        } catch (\Exception $e) {
            Log::error('[Gemini] Exception in analyzeFinance', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return $this->generateLocalRoasting($dailyBudget, $user->name);
        }
    }

    /**
     * Generate roasting berbasis logic lokal tanpa API call
     *
     * Stratifikasi berdasarkan nominal jatah harian:
     * - > 700rb: Positif tapi ingatkan jangan khilaf
     * - 300-700rb: Netral, hati-hati belanja
     * - < 300rb: Tough love, harus hemat parah
     *
     * @param float $dailyBudget
     * @param string $userName
     * @return string Pesan roasting random dari kategori yang sesuai
     */
    private function generateLocalRoasting(float $dailyBudget, string $userName): string
    {
        $roastings = [];

        if ($dailyBudget > 700000) {
            $roastings = [
                "Wah, Rp " . number_format($dailyBudget, 0, ',', '.') . " hari? Jangan sampe ilang di coffee shop!",
                "Lumayan lah jatahnya, tapi jangan sombong ya!",
                "Boleh jajan santai, tapi tetap irit biar sampai akhir bulan.",
                "Jatah gede gini, harusnya udah bisa nabung buat keperluan darurat.",
                "Cukup lah buat hidup santai, asalkan jangan impulsif beli barang yang nggak perlu."
            ];
        } elseif ($dailyBudget >= 300000) {
            $roastings = [
                "Rp " . number_format($dailyBudget, 0, ',', '.') . " sehari, cukupan sih tapi hati-hati belanja online!",
                "Jatah pas-pasan, jadi harus smart dalam spending.",
                "Bisa makan 3x sehari, tapi mesti hemat sama nraktir temen.",
                "Lumayan, tapi kalau nonton bioskop harus tahan lapar dulu.",
                "Cukupan untuk basic needs, sisanya tabung aja."
            ];
        } else {
            $roastings = [
                "Aduh, Rp " . number_format($dailyBudget, 0, ',', '.') . " doang sehari? Makan nasi kucing aja deh!",
                "Pas-pasan banget! Harus super hemat jangan sampe minus.",
                "Ini bukan jatah, ini adalah tantangan survival finansial!",
                "Makan murah meriah aja, atau share sama temen biar hemat.",
                "Yang sabar ya, tinggal nahan sampe gajian berikutnya!"
            ];
        }

        $selectedRoasting = $roastings[array_rand($roastings)];
        Log::info('[Gemini] Local roasting selected', ['message' => $selectedRoasting]);

        return $selectedRoasting;
    }

    /**
     * Ekstrak data transaksi dari input natural language menggunakan AI
     *
     * Fallback: Jika API gagal, gunakan regex parsing lokal
     *
     * @param string $inputText Teks natural language (contoh: "makan ayam 25rb")
     * @return array Associative array dengan keys: amount, category_id, pocket_id, description
     */
    public function extractTransaction(string $inputText): array
    {
        $apiKey = env('GEMINI_API_KEY');

        if (empty($apiKey)) {
            Log::warning('[Gemini] API Key missing for extractTransaction');
            return $this->fallbackParse($inputText);
        }

        try {
            // Prepare category dan pocket reference untuk AI
            $categories = Category::all()->map(fn($c) => "ID {$c->id}: {$c->name}")->join(", ");
            $pockets = Pocket::all()->map(fn($p) => "ID {$p->id}: {$p->name}")->join(", ");

            $prompt = <<<PROMPT
                        Ekstrak JSON dari teks ini: "{$inputText}"

                        Referensi Kategori ID: [{$categories}]
                        Referensi Pocket ID: [{$pockets}]

                        Output harus JSON valid dengan struktur:
                        {
                        "amount": 15000,
                        "category_id": 1,
                        "pocket_id": null,
                        "description": "Makan Ayam"
                        }

                        CATATAN:
                        - amount dalam Rupiah (angka saja, tanpa titik)
                        - category_id pilih yang paling relevan
                        - pocket_id boleh null (user pilih sendiri)
                        - description gunakan PascalCase
                    PROMPT;

            Log::info('[Gemini] Attempting extractTransaction');

            $response = Http::withoutVerifying()
                ->timeout(10)
                ->post(
                    self::API_ENDPOINT . '/gemini-2.5-flash-lite:generateContent?key=' . $apiKey,
                    ['contents' => [['parts' => [['text' => $prompt]]]]]
                );

            if ($response->failed()) {
                Log::warning('[Gemini] extractTransaction API failed', ['status' => $response->status()]);
                return $this->fallbackParse($inputText);
            }

            $result = $response->json();
            $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

            // Extract JSON dari response
            if (preg_match('/\{.*?\}/s', $text, $matches)) {
                $json = json_decode($matches[0], true);
                if ($json && isset($json['amount']) && $json['amount'] > 0) {
                    Log::info('[Gemini] Extraction success', ['amount' => $json['amount']]);
                    return $json;
                }
            }

            Log::warning('[Gemini] Could not parse JSON from response');
            return $this->fallbackParse($inputText);

        } catch (\Exception $e) {
            Log::error('[Gemini] Exception in extractTransaction', [
                'message' => $e->getMessage(),
                'input' => $inputText
            ]);
            return $this->fallbackParse($inputText);
        }
    }

    /**
     * Parse transaksi menggunakan regex ketika API tidak tersedia
     *
     * Contoh parsing:
     * - "makan ayam 25rb" → amount: 25000, description: "Makan Ayam"
     * - "beli buku 50k" → amount: 50000, description: "Beli Buku"
     *
     * @param string $text
     * @return array
     */
    private function fallbackParse(string $text): array
    {
        // Cari pattern: angka + optional (rb|k|ribu)
        preg_match('/(\d+)(?:\s|\.)*(rb|k|ribu)?/i', $text, $matches);

        $amount = 0;
        if (!empty($matches)) {
            $num = (int) str_replace('.', '', $matches[1]);
            $multiplier = isset($matches[2]) && in_array(strtolower($matches[2]), ['rb', 'k', 'ribu']) ? 1000 : 1;
            $amount = $num * $multiplier;
        }

        // Bersihkan angka dari text untuk deskripsi
        $cleanDesc = trim(preg_replace('/(\d+)(rb|k|ribu)?/i', '', $text));

        Log::info('[Gemini] Fallback parse used', [
            'input' => $text,
            'amount' => $amount,
            'description' => $cleanDesc
        ]);

        return [
            'amount' => $amount,
            'category_id' => null,
            'pocket_id' => null,
            'description' => ucwords($cleanDesc) ?: 'Pengeluaran'
        ];
    }
}
