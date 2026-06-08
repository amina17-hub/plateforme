<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('artisans:sync-metrics-from-csv', function () {
    $csvPath = storage_path('skikda_unified_dataset.csv');

    if (! file_exists($csvPath)) {
        $this->error("CSV introuvable: {$csvPath}");
        return self::FAILURE;
    }

    $handle = fopen($csvPath, 'r');
    if (! $handle) {
        $this->error('Impossible d’ouvrir le fichier CSV.');
        return self::FAILURE;
    }

    $headers = fgetcsv($handle);
    if (! $headers) {
        fclose($handle);
        $this->error('CSV vide ou invalide.');
        return self::FAILURE;
    }

    $recordsByKey = [];
    $serviceMetrics = [];

    while (($row = fgetcsv($handle)) !== false) {
        $record = array_combine($headers, $row);
        if (! $record) {
            continue;
        }

        $key = implode('|', [
            trim((string) ($record['service_type'] ?? '')),
            trim((string) ($record['city'] ?? '')),
            number_format((float) ($record['latitude'] ?? 0), 4, '.', ''),
            number_format((float) ($record['longitude'] ?? 0), 4, '.', ''),
        ]);

        $recordsByKey[$key]['prices'][] = (float) ($record['price'] ?? 0);
        $recordsByKey[$key]['ratings'][] = (float) ($record['rating'] ?? 0);

        $serviceKey = trim((string) ($record['service_type'] ?? ''));
        $serviceMetrics[$serviceKey]['prices'][] = (float) ($record['price'] ?? 0);
        $serviceMetrics[$serviceKey]['ratings'][] = (float) ($record['rating'] ?? 0);
    }

    fclose($handle);

    foreach ($recordsByKey as $key => $metrics) {
        sort($metrics['prices']);
        sort($metrics['ratings']);

        $priceIndex = intdiv(count($metrics['prices']), 2);
        $ratingIndex = intdiv(count($metrics['ratings']), 2);

        $recordsByKey[$key] = [
            'price' => $metrics['prices'][$priceIndex] ?? 0,
            'rating' => $metrics['ratings'][$ratingIndex] ?? 0,
        ];
    }

    foreach ($serviceMetrics as $serviceKey => $metrics) {
        sort($metrics['prices']);
        sort($metrics['ratings']);

        $priceIndex = intdiv(count($metrics['prices']), 2);
        $ratingIndex = intdiv(count($metrics['ratings']), 2);

        $serviceMetrics[$serviceKey] = [
            'price' => $metrics['prices'][$priceIndex] ?? 0,
            'rating' => $metrics['ratings'][$ratingIndex] ?? 0,
        ];
    }

    $updatedCount = 0;
    $matchedCount = 0;
    $unchangedCount = 0;
    $missingMatchCount = 0;

    DB::table('artisans')
        ->select('id', 'name', 'service_type', 'city', 'latitude', 'longitude', 'price', 'rating')
        ->orderBy('id')
        ->chunkById(500, function ($artisans) use (&$updatedCount, &$matchedCount, &$unchangedCount, &$missingMatchCount, $recordsByKey, $serviceMetrics) {
            foreach ($artisans as $artisan) {
                $key = implode('|', [
                    trim((string) ($artisan->service_type ?? '')),
                    trim((string) ($artisan->city ?? '')),
                    number_format((float) ($artisan->latitude ?? 0), 4, '.', ''),
                    number_format((float) ($artisan->longitude ?? 0), 4, '.', ''),
                ]);

                $serviceKey = trim((string) ($artisan->service_type ?? ''));
                $source = $recordsByKey[$key] ?? null;

                if ($source) {
                    $matchedCount++;
                } elseif (isset($serviceMetrics[$serviceKey])) {
                    $source = $serviceMetrics[$serviceKey];
                } else {
                    $missingMatchCount++;
                    continue;
                }

                $payload = [];

                if ($artisan->price === null || $artisan->price === '') {
                    $payload['price'] = $source['price'];
                }

                if ($artisan->rating === null || $artisan->rating === '') {
                    $payload['rating'] = $source['rating'];
                }

                if (empty($payload)) {
                    $unchangedCount++;
                    continue;
                }

                DB::table('artisans')
                    ->where('id', $artisan->id)
                    ->update($payload);

                $updatedCount++;
            }
        });

    $this->info("Artisans relies au CSV: {$matchedCount}");
    $this->info("Artisans mis a jour: {$updatedCount}");
    $this->info("Artisans deja complets: {$unchangedCount}");
    $this->info("Artisans sans correspondance CSV: {$missingMatchCount}");

    return self::SUCCESS;
})->purpose('Remplit price et rating manquants des artisans depuis le CSV');
