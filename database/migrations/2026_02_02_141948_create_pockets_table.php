<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pockets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');

            $table->enum('type', [
                'main',
                'savings',
                'emergency',
                'wishlist',
            ]);

            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('target_amount', 15, 2)->nullable();

            $table->boolean('is_completed')->default(false);
            $table->boolean('is_locked')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pockets');
    }
};
