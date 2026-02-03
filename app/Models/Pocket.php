<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pocket extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'balance'        => 'decimal:2',
        'target_amount'  => 'decimal:2',
        'is_completed'   => 'boolean',
        'is_locked'      => 'boolean',
    ];

    /**
     * Relasi: Pocket → User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: Pocket → Transactions
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
