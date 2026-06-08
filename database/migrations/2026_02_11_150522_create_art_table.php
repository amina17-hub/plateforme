
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('art', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // lien avec users
            $table->string('metier')->nullable();
            $table->integer('experience')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
        });
    }

public function down(): void
{
    Schema::table('art', function (Blueprint $table) {
        $table->dropColumn(['photo', 'metier', 'experience']);
    });
}
};
