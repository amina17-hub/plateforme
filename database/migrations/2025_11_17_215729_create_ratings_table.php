<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id(); 
            
            // الروابط الخارجية (Foreign Keys)
            // يربط بتقييم العميل (client_id)
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            // يربط بتقييم الحرفي (artisan_id)
            $table->foreignId('artisan_id')->constrained('artisans')->onDelete('cascade');
            
            $table->decimal('rating', 2, 1); // التقييم (مثلاً 4.5)
            $table->text('comment')->nullable();
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};