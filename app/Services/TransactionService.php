<?php

namespace App\Services;

use App\Models\Pocket;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class TransactionService
{
    /**
     * Pengeluaran (saldo tidak boleh minus)
     */
    public function expense(array $data): void
    {
        DB::transaction(function () use ($data) {

            $pocket = Pocket::where('user_id', $data['user_id'])
                ->lockForUpdate()
                ->findOrFail($data['pocket_id']);

            if ($pocket->balance < $data['amount']) {
                throw new Exception('Saldo pocket tidak mencukupi');
            }

            Transaction::create([
                'user_id'     => $data['user_id'],
                'pocket_id'   => $pocket->id,
                'category_id' => $data['category_id'],
                'amount'      => $data['amount'],
                'date'        => $data['date'],
                'description' => $data['description'] ?? null,
                'type'        => 'expense',
            ]);

            $pocket->decrement('balance', $data['amount']);
        });
    }

    /**
     * Income dengan split allocation
     */
    public function incomeSplit(array $data): void
    {
        DB::transaction(function () use ($data) {

            $user    = User::findOrFail($data['user_id']);
            $amount  = $data['amount'];
            $batchId = Str::uuid();

            $pockets = $user->pockets()
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $totalAllocated = 0;

            foreach ($data['allocations'] ?? [] as $pocketId => $nominal) {
                if ($nominal <= 0) continue;

                if (!isset($pockets[$pocketId])) {
                    throw new Exception('Pocket tidak valid');
                }

                if ($pockets[$pocketId]->type === 'main') {
                    throw new Exception('Tidak perlu alokasi ke dompet utama');
                }

                $totalAllocated += $nominal;

                Transaction::create([
                    'user_id'       => $user->id,
                    'pocket_id'     => $pocketId,
                    'amount'        => $nominal,
                    'type'          => 'income',
                    'income_source' => $data['source'],
                    'date'          => $data['date'],
                    'batch_id'      => $batchId,
                    'description'   => 'Alokasi pemasukan',
                ]);

                $pockets[$pocketId]->increment('balance', $nominal);
            }

            if ($totalAllocated > $amount) {
                throw new Exception('Total alokasi melebihi pemasukan');
            }

            // sisa ke dompet utama
            $remaining = $amount - $totalAllocated;

            if ($remaining > 0) {
                $mainPocket = $pockets->firstWhere('type', 'main');

                if (!$mainPocket) {
                    throw new Exception('Dompet utama tidak ditemukan');
                }

                Transaction::create([
                    'user_id'       => $user->id,
                    'pocket_id'     => $mainPocket->id,
                    'amount'        => $remaining,
                    'type'          => 'income',
                    'income_source' => $data['source'],
                    'date'          => $data['date'],
                    'batch_id'      => $batchId,
                    'description'   => 'Sisa pemasukan ke dompet harian',
                ]);

                $mainPocket->increment('balance', $remaining);
            }
        });
    }

    /**
     * Transfer antar pocket
     */
    public function transfer(array $data): void
    {
        DB::transaction(function () use ($data) {

            if ($data['from_pocket_id'] === $data['to_pocket_id']) {
                throw new Exception('Pocket asal dan tujuan tidak boleh sama');
            }

            $from = Pocket::where('user_id', $data['user_id'])
                ->lockForUpdate()
                ->findOrFail($data['from_pocket_id']);

            $to = Pocket::where('user_id', $data['user_id'])
                ->lockForUpdate()
                ->findOrFail($data['to_pocket_id']);

            if ($from->balance < $data['amount']) {
                throw new Exception('Saldo pocket asal tidak mencukupi');
            }

            $batchId = Str::uuid();

            Transaction::create([
                'user_id'   => $data['user_id'],
                'pocket_id' => $from->id,
                'amount'    => $data['amount'],
                'type'      => 'transfer_out',
                'date'      => $data['date'],
                'batch_id'  => $batchId,
            ]);

            Transaction::create([
                'user_id'   => $data['user_id'],
                'pocket_id' => $to->id,
                'amount'    => $data['amount'],
                'type'      => 'transfer_in',
                'date'      => $data['date'],
                'batch_id'  => $batchId,
            ]);

            $from->decrement('balance', $data['amount']);
            $to->increment('balance', $data['amount']);
        });
    }
}
