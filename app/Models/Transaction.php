<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',         // Biar format tanggal aman
        'amount' => 'decimal:2',  // Biar angka uang presisi
        'is_ai_generated' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pocket()
    {
        return $this->belongsTo(Pocket::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
