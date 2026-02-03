<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('pocket_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->date('date');

            $table->enum('type', [
                'income',
                'expense',
                'transfer_in',
                'transfer_out',
            ]);

            $table->enum('income_source', ['routine', 'bonus'])->nullable();

            $table->uuid('batch_id')->nullable();

            $table->boolean('is_ai_generated')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'date']);
            $table->index(['pocket_id', 'date']);
            $table->index(['pocket_id', 'type']);
            $table->index('batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
