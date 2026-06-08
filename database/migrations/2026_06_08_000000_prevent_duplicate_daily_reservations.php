<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateGroups = DB::table('reservations')
            ->select('client_user_id', 'artisan_id', 'reservation_date')
            ->groupBy('client_user_id', 'artisan_id', 'reservation_date')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateGroups as $group) {
            $duplicateIds = DB::table('reservations')
                ->where('client_user_id', $group->client_user_id)
                ->where('artisan_id', $group->artisan_id)
                ->where('reservation_date', $group->reservation_date)
                ->orderBy('id')
                ->pluck('id')
                ->slice(1);

            if ($duplicateIds->isNotEmpty()) {
                DB::table('reservations')->whereIn('id', $duplicateIds)->delete();
            }
        }

        Schema::table('reservations', function (Blueprint $table) {
            $table->unique(
                ['client_user_id', 'artisan_id', 'reservation_date'],
                'reservations_client_artisan_date_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropUnique('reservations_client_artisan_date_unique');
        });
    }
};
