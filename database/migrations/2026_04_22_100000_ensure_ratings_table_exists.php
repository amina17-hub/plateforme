<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ratings')) {
            return;
        }

        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('artisan_id')->constrained('artisans')->cascadeOnDelete();
            $table->decimal('rating', 2, 1);
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['client_id', 'artisan_id']);
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('ratings')) {
            Schema::dropIfExists('ratings');
        }
    }
};
