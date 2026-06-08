<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id(); 
            
            // يربط الخدمة بالحرفي (artisan_id)
            $table->foreignId('artisan_id')->constrained('artisans')->onDelete('cascade');
            
            $table->string('title', 100); 
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2); 
            $table->string('city', 50); 
            $table->double('latitude')->nullable(); 
            $table->double('longitude')->nullable();
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};