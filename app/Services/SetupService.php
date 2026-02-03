<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class SetupService
{
    /**
     * Handle setup awal user (salary date & saldo pocket)
     */
    public function handle(User $user, array $data): void
    {
        DB::transaction(function () use ($user, $data) {

            // Update tanggal gajian
            $user->update([
                'salary_date' => $data['salary_date'],
            ]);

            // Update saldo pocket utama
            $this->updatePocket($user, 'main', [
                'balance' => $data['main_balance'],
            ]);

            // Savings
            $this->updatePocket($user, 'savings', [
                'balance' => $data['savings_balance'] ?? 0,
            ]);

            // Emergency
            $this->updatePocket($user, 'emergency', [
                'balance' => $data['emergency_balance'] ?? 0,
            ]);

            // Wishlist (optional)
            // $this->updatePocket($user, 'wishlist', [
            //     'balance' => $data['wishlist_balance'] ?? 0,
            //     'target_amount' => $data['wishlist_target'] ?? 0,
            // ]);
        });
    }

    /**
     * Helper update pocket by type
     */
    protected function updatePocket(User $user, string $type, array $payload): void
    {
        $pocket = $user->pockets()->where('type', $type)->first();

        if (!$pocket) {
            throw new \Exception("Pocket {$type} tidak ditemukan");
        }

        $pocket->update($payload);
    }
}
