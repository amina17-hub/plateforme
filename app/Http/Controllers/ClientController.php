<?php

namespace App\Http\Controllers;

use App\Models\Artisan;
use App\Support\SkikdaCommunes;

class ClientController extends Controller
{
    public function dashboard()
    {
        $services = Artisan::query()
            ->whereNotNull('service_type')
            ->where('service_type', '!=', '')
            ->select('service_type')
            ->distinct()
            ->orderBy('service_type')
            ->pluck('service_type');
        $communes = SkikdaCommunes::filter(Artisan::query()
            ->whereNotNull('commune')
            ->where('commune', '!=', '')
            ->select('commune')
            ->distinct()
            ->orderBy('commune')
            ->pluck('commune'));
        $currentUserName = auth()->user()->name;
        return view('client.dashboard', compact('services', 'communes', 'currentUserName'));
    }
}
