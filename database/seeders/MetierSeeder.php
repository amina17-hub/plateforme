<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MetierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $category = \App\Models\Category::firstOrCreate(['name' => 'Services']);

        $metiers = [
            'Soutien Scolaire',
            "Décorateur d'intérieur",
            'Dératiseur / Désinsectiseur',
            'Piscines (Constructeur/Maintenance)',
            'Fabrication de Bougies',
            'Restauration de vêtements',
            'Installateur Systèmes Sécurité',
            'Carreleur',
            'Vitrailler / Miroitier',
            'Peintre',
            'Ébéniste',
            'Charpentier',
            'Nettoyage professionnel (Spécialisé)',
            'Menuisier',
            'Antenniste',
            'Serrurier',
            'Aide-Ménagère Spécialisée',
            'Fabrication de parfums',
            'Coiffure à Domicile',
            'Plâtrier / Staffeur',
            'Esthéticienne à Domicile',
            "Organisation d'événements",
            'Électricien',
            'Traiteur / Pâtissier',
            'Cosmétiques (Crèmes hydratantes)',
            'Climatisation / Frigoriste',
            'Maçon',
            'Tapissier / Couturier (Linge de Maison)',
            'Plombier',
            'Chauffagiste',
            'Réparation Électroménager',
            'Jardinier / Paysagiste',
            'Étanchéiste',
        ];

        foreach ($metiers as $metier) {
            \App\Models\Metier::create([
                'name' => $metier,
                'category_id' => $category->id
            ]);
        }
    }
}
