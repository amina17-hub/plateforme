<?php
namespace App\Http\Controllers\Public;
use App\Http\Controllers\Controller;
use App\Models\Artisan;
use App\Support\SkikdaCommunes;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Metier;

class MetierController extends Controller
{
    public function index()
    {
        $categories = Category::with('metiers')->withCount('metiers')->get();
        $communes = SkikdaCommunes::filter(Artisan::query()
            ->whereNotNull('commune')
            ->where('commune', '!=', '')
            ->distinct()
            ->orderBy('commune')
            ->pluck('commune'));

        return view('public.metiers.index', compact('categories', 'communes'));
    }

    // route pour recherche AJAX
    public function search(Request $request)
    {
        $q = $request->query('q');
        $results = Metier::where('name', 'like', "%{$q}%")
                    ->with('category')
                    ->limit(10)
                    ->get();

        return response()->json($results);
    }
}
