<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('ratings');
        Schema::dropIfExists('suggestions');
        Schema::dropIfExists('services');
    }

    public function down(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artisan_id')->constrained('artisans')->onDelete('cascade');
            $table->string('title', 100);
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2);
            $table->string('city', 50);
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->timestamps();
        });

        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('artisan_id')->constrained('artisans')->onDelete('cascade');
            $table->decimal('rating', 2, 1);
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        Schema::create('suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('title', 100);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }
};
