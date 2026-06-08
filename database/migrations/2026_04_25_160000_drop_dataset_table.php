<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('dataset');
    }

    public function down(): void
    {
        Schema::create('dataset', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('ville');
            $table->integer('valeur');
            $table->timestamps();
        });
    }
};
