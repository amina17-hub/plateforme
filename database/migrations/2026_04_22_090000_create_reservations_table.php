<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('artisan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('artisan_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('client_name');
            $table->string('artisan_name');
            $table->string('service_type');
            $table->string('city')->nullable();
            $table->decimal('quoted_price', 10, 2)->nullable();
            $table->date('reservation_date');
            $table->string('reservation_time', 5);
            $table->text('notes')->nullable();
            $table->string('status')->default('en_attente');
            $table->timestamps();

            $table->index(['client_user_id', 'reservation_date']);
            $table->index(['artisan_id', 'reservation_date']);
            $table->index(['artisan_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
