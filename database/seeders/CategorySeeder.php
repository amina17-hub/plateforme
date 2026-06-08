<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Construction et Bâtiment',
            'Services Domestiques',
            'Beauté et Bien-être',
            'Alimentation et Restauration',
            'Électronique et Réparation',
            'Éducation et Formation',
            'Événements et Loisirs',
            'Autres',
        ];

        foreach ($categories as $category) {
            \App\Models\Category::create(['name' => $category]);
        }
    }
}
