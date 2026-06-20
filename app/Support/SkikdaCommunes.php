<?php

namespace App\Support;

use Illuminate\Support\Collection;

class SkikdaCommunes
{
    private const COMMUNES = [
        'Aïn Bouziane',
        'Aïn Charchar',
        'Aïn Kechra',
        'Aïn Zouit',
        'Azzaba',
        'Bekkouche Lakhdar',
        'Ben Azzouz',
        'Beni Bechir',
        'Beni Oulbane',
        'Beni Zid',
        'Bin El Ouiden',
        'Bouchtata',
        'Cheraïa',
        'Collo',
        'Djendel Saadi Mohamed',
        'El Ghedir',
        'El Hadaiek',
        'El Harrouch',
        'El Marsa',
        'Emdjez Edchich',
        'Es Sebt',
        'Filfila',
        'Hamadi Krouma',
        'Kanoua',
        'Kerkera',
        'Kheneg Mayoum',
        'Oued Zehour',
        'Ouldja Boulballout',
        'Oum Toub',
        'Ouled Attia',
        'Ouled Hbaba',
        'Ramdane Djamel',
        'Salah Bouchaour',
        'Sidi Mezghiche',
        'Skikda',
        'Tamalous',
        'Zerdaza',
        'Zitouna',
    ];

    public static function all(): Collection
    {
        return collect(self::COMMUNES);
    }

    public static function filter(Collection $communes): Collection
    {
        return $communes
            ->map(fn ($commune) => trim((string) $commune))
            ->filter(fn ($commune) => self::all()->containsStrict($commune))
            ->unique()
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }
}
