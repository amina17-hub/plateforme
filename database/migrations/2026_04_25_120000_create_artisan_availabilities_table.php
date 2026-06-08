<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artisan_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artisan_id')->constrained()->cascadeOnDelete();
            $table->date('available_date');
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->unique(['artisan_id', 'available_date']);
            $table->index(['available_date', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artisan_availabilities');
    }
};
