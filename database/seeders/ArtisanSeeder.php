<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

class ArtisanSeeder extends Seeder
{
    public function run(): void
    {
        // Nettoyer les artisans existants avant de réimporter les profils.
        Schema::disableForeignKeyConstraints();
        DB::table('artisans')->truncate();
        Schema::enableForeignKeyConstraints();

        $csvFile = storage_path('skikda_unified_dataset2.csv');
        if (!file_exists($csvFile)) {
            $this->command->error("CSV file not found at: " . $csvFile);
            return;
        }

        $file = fopen($csvFile, 'r');
        $header = fgetcsv($file);

        if ($header === false) {
            fclose($file);
            $this->command->error("CSV file is empty: " . $csvFile);
            return;
        }

        $header = array_map(function ($column) {
            return preg_replace('/^\xEF\xBB\xBF/', '', trim((string) $column));
        }, $header);

        $requiredColumns = [
            'artisan_id',
            'artisan_name',
            'service_type',
            'description',
            'city',
            'commune',
            'latitude',
            'longitude',
        ];

        $missingColumns = array_diff($requiredColumns, $header);
        if ($missingColumns !== []) {
            fclose($file);
            $this->command->error(
                'CSV is missing required columns: ' . implode(', ', $missingColumns)
            );
            return;
        }

        $defaultPassword = Hash::make('password');
        $records = [];

        while (($row = fgetcsv($file)) !== false) {
            if (count($row) !== count($header)) {
                continue;
            }

            $data = array_combine($header, $row);
            if ($data === false || empty($data['artisan_id'])) {
                continue;
            }

            $records[$data['artisan_id']] = $data;
        }

        foreach ($records as $artisanId => $record) {
            $email = sprintf('artisan%s@seed.local', $artisanId);
            $timestamp = $record['created_at'] ?? now();

            DB::table('users')->updateOrInsert(
                ['email' => $email],
                [
                    'password' => $defaultPassword,
                    'role' => 'artisan',
                    'created_at' => $timestamp,
                    'updated_at' => now(),
                ]
            );

            $userId = DB::table('users')
                ->where('email', $email)
                ->value('id');

            if ($userId === null) {
                continue;
            }

            DB::table('artisans')->insert([
                'user_id' => $userId,
                'name' => $record['artisan_name'],
                'service_type' => $record['service_type'],
                'description' => $record['description'],
                'city' => $record['city'],
                'commune' => $record['commune'] ?: null,
                'latitude' => $record['latitude'] !== '' ? (float) $record['latitude'] : null,
                'longitude' => $record['longitude'] !== '' ? (float) $record['longitude'] : null,
                'created_at' => $timestamp,
                'updated_at' => now(),
            ]);
        }

        fclose($file);
        $this->command->info('Artisans data seeded successfully from storage/skikda_unified_dataset2.csv!');
    }
}
