<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeRatingNullableInArtisansTable extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('artisans', 'rating')) {
            Schema::table('artisans', function (Blueprint $table) {
                $table->decimal('rating', 3, 2)->nullable()->change();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('artisans', 'rating')) {
            Schema::table('artisans', function (Blueprint $table) {
                $table->decimal('rating', 3, 2)->nullable(false)->change();
            });
        }
    }
}

