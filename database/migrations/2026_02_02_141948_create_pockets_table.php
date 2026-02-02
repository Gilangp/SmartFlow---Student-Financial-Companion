<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pockets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // User hapus = Pocket hapus

            $table->string('name'); // "Dompet Harian", "Tabungan Laptop"

            // INI KOLOM TERPENTING: Menentukan logika bisnis
            $table->enum('type', ['main', 'emergency', 'savings', 'wishlist']);

            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('target_amount', 15, 2)->nullable(); // Target (Wajib untuk Wishlist/Emergency)

            // Status
            $table->boolean('is_completed')->default(false); // Jika Wishlist tercapai
            $table->boolean('is_locked')->default(false);    // Jika Emergency/Savings (peringatan saat ditarik)

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pockets');
    }
};
