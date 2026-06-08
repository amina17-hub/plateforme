<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Artisan;
use Illuminate\Support\Facades\DB;

class CorrectAllArtisansCoordinates extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // قائمة الإحداثيات المركزية لولايات الجزائر الـ 58
        $wilaya_coordinates = [
            'Adrar' => ['lat' => 27.8744, 'lon' => 0.2929], 'Chlef' => ['lat' => 36.1667, 'lon' => 1.3333],
            'Laghouat' => ['lat' => 33.7933, 'lon' => 2.8711], 'Oum El Bouaghi' => ['lat' => 35.8833, 'lon' => 7.1167],
            'Batna' => ['lat' => 35.5500, 'lon' => 6.1833], 'Bejaia' => ['lat' => 36.7500, 'lon' => 5.0833],
            'Biskra' => ['lat' => 34.8500, 'lon' => 5.7167], 'Bechar' => ['lat' => 31.6167, 'lon' => -2.2000],
            'Blida' => ['lat' => 36.4833, 'lon' => 2.8333], 'Bouira' => ['lat' => 36.3667, 'lon' => 3.7667],
            'Tamanrasset' => ['lat' => 22.7850, 'lon' => 5.5228], 'Tebessa' => ['lat' => 35.4000, 'lon' => 8.1167],
            'Tlemcen' => ['lat' => 34.8833, 'lon' => -1.3167], 'Tiaret' => ['lat' => 35.3500, 'lon' => 1.3167],
            'Tizi Ouzou' => ['lat' => 36.7167, 'lon' => 4.0500], 'Alger' => ['lat' => 36.7538, 'lon' => 3.0588],
            'Djelfa' => ['lat' => 34.6667, 'lon' => 3.2500], 'Jijel' => ['lat' => 36.8167, 'lon' => 5.7667],
            'Setif' => ['lat' => 36.1833, 'lon' => 5.3833], 'Saida' => ['lat' => 34.8333, 'lon' => 0.1500],
            'Skikda' => ['lat' => 36.8833, 'lon' => 6.9000], 'Sidi Bel Abbes' => ['lat' => 35.2000, 'lon' => -0.6333],
            'Annaba' => ['lat' => 36.9000, 'lon' => 7.7667], 'Guelma' => ['lat' => 36.4667, 'lon' => 7.4333],
            'Constantine' => ['lat' => 36.3650, 'lon' => 6.6147], 'Medea' => ['lat' => 36.2667, 'lon' => 2.7667],
            'Mostaganem' => ['lat' => 35.9333, 'lon' => 0.0833], 'Msila' => ['lat' => 35.7000, 'lon' => 4.5167],
            'Mascara' => ['lat' => 35.3833, 'lon' => 0.1500], 'Ouargla' => ['lat' => 31.9500, 'lon' => 5.3333],
            'Oran' => ['lat' => 35.6972, 'lon' => -0.6300], 'El Bayadh' => ['lat' => 33.6833, 'lon' => 1.0167],
            'Illizi' => ['lat' => 26.4719, 'lon' => 8.4658], 'Bordj Bou Arreridj' => ['lat' => 36.0667, 'lon' => 4.7667],
            'Boumerdes' => ['lat' => 36.7500, 'lon' => 3.4667], 'El Tarf' => ['lat' => 36.7500, 'lon' => 8.3167],
            'Tindouf' => ['lat' => 27.6719, 'lon' => -8.1478], 'Tissemsilt' => ['lat' => 35.6000, 'lon' => 1.7667],
            'El Oued' => ['lat' => 33.3667, 'lon' => 6.8667], 'Khenchela' => ['lat' => 35.4333, 'lon' => 7.1500],
            'Souk Ahras' => ['lat' => 36.2833, 'lon' => 7.9500], 'Tipaza' => ['lat' => 36.5667, 'lon' => 2.4500],
            'Mila' => ['lat' => 36.4500, 'lon' => 6.2667], 'Ain Defla' => ['lat' => 36.2667, 'lon' => 1.9500],
            'Naama' => ['lat' => 33.2000, 'lon' => -0.3167], 'Ain Temouchent' => ['lat' => 35.3000, 'lon' => -1.1333],
            'Ghardaia' => ['lat' => 32.4833, 'lon' => 3.6667], 'Relizane' => ['lat' => 35.7500, 'lon' => 0.7000],
            'Timimoun' => ['lat' => 29.2667, 'lon' => 0.2333], 'Bordj Badji Mokhtar' => ['lat' => 21.3667, 'lon' => 0.8667],
            'Ouled Djellal' => ['lat' => 34.3333, 'lon' => 5.0833], "Beni Abbes" => ['lat' => 30.1333, 'lon' => -2.1833],
            'In Salah' => ['lat' => 27.2000, 'lon' => 2.4833], 'In Guezzam' => ['lat' => 19.5667, 'lon' => 5.7667],
            'Touggourt' => ['lat' => 33.1000, 'lon' => 6.0500], 'Djanet' => ['lat' => 24.5500, 'lon' => 9.4833],
            "El M'Ghair" => ['lat' => 34.8000, 'lon' => 6.0333], 'El Meniaa' => ['lat' => 30.5667, 'lon' => 2.8833],
            'In Amenas' => ['lat' => 28.0500, 'lon' => 9.6167], 'Mekla' => ['lat' => 36.7000, 'lon' => 4.2500], 
        ];

        DB::beginTransaction();
        try {
            // 1. التحديث بالإحداثيات المركزية
            foreach ($wilaya_coordinates as $city => $coords) {
                Artisan::where('city', $city)
                    ->update([
                        'latitude' => $coords['lat'],
                        'longitude' => $coords['lon'],
                    ]);
            }

            // 2. إضافة تباعد عشوائي بسيط لبعض الحرفيين لضمان ظهور نقاط منفصلة على الخريطة
            Artisan::all()->each(function ($artisan) use ($wilaya_coordinates) {
                $city = $artisan->city;
                if (isset($wilaya_coordinates[$city])) {
                    $baseLat = $wilaya_coordinates[$city]['lat'];
                    $baseLon = $wilaya_coordinates[$city]['lon'];
                    
                    $randomLatDiff = mt_rand(-5, 5) / 1000.0; 
                    $randomLonDiff = mt_rand(-5, 5) / 1000.0; 
                    
                    $artisan->latitude = $baseLat + $randomLatDiff;
                    $artisan->longitude = $baseLon + $randomLonDiff;
                    $artisan->save();
                }
            });

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // يمكنك طباعة رسالة الخطأ هنا إذا كنت بحاجة إليها
        }
    }
}