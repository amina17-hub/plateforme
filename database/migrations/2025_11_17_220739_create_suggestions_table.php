<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suggestions', function (Blueprint $table) {
            $table->id(); 
            
            // يربط الاقتراح بالعميل (client_id)
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            
            $table->string('title', 100); 
            $table->text('description')->nullable();
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suggestions');
    }
};