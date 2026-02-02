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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('pocket_id')->constrained()->onDelete('cascade'); // Uang ini ada di kantong mana?

            // Jika kategori dihapus, transaksinya jangan hilang, tapi jadi "Uncategorized" (null)
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();

            $table->string('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->date('date');

            // Tipe Transaksi
            $table->enum('type', ['income', 'expense']);

            // Khusus Pemasukan (Income)
            $table->enum('income_source', ['routine', 'bonus'])->nullable(); // Rutin (Ortu) vs Bonus (Freelance)

            // Fitur Canggih: Batch ID
            // Digunakan saat fitur "Auto Split" memecah 1 pemasukan menjadi 4 transaksi berbeda
            $table->uuid('batch_id')->nullable();

            // Penanda AI
            $table->boolean('is_ai_generated')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
