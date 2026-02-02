<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pocket extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id']; // Semua kolom boleh diisi kecuali ID

    // Relasi: Dompet milik satu User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi: Dompet punya banyak riwayat transaksi
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
