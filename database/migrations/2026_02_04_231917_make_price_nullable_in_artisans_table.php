<?php

   use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakePriceNullableInArtisansTable extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('artisans', 'price')) {
            Schema::table('artisans', function (Blueprint $table) {
                $table->decimal('price', 10, 2)->nullable()->change();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('artisans', 'price')) {
            Schema::table('artisans', function (Blueprint $table) {
                $table->decimal('price', 10, 2)->nullable(false)->change();
            });
        }
    }
}
