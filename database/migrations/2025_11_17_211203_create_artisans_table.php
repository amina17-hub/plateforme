<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تشغيل عملية الـ Migrations (إنشاء الجدول).
     */
    public function up(): void
    {
     Schema::create('artisans', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->string('service_type');
    $table->text('description')->nullable();
    $table->string('city');
    $table->string('commune')->nullable();
    $table->decimal('latitude', 10, 7)->nullable();
    $table->decimal('longitude', 10, 7)->nullable();
    $table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('artisans');
    }
};