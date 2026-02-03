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
        'date'            => 'date',
        'amount'          => 'decimal:2',
        'is_ai_generated' => 'boolean',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    /**
     * Relasi: Transaction → User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: Transaction → Pocket
     */
    public function pocket()
    {
        return $this->belongsTo(Pocket::class);
    }

    /**
     * Relasi: Transaction → Category (nullable untuk income)
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
