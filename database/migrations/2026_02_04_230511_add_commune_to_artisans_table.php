<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommuneToArtisansTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('artisans', 'commune')) {
            Schema::table('artisans', function (Blueprint $table) {
                $table->string('commune')->nullable()->after('city');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('artisans', 'commune')) {
            Schema::table('artisans', function (Blueprint $table) {
                $table->dropColumn('commune');
            });
        }
    }
}
