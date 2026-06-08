<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Art;
use App\Models\Artisan;
use App\Support\PortfolioImageProcessor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ArtisanController extends Controller
{
    public function dashboard()
    {
        $profile = Art::where('user_id', Auth::id())
            ->whereNull('title')
            ->first();

        if (!$profile) {
            $profile = new Art([
                'user_id' => Auth::id(),
            ]);
        }

        $artisanService = Auth::user()->artisan?->service_type;

        return view('artisan.dashboard', [
            'profile' => $profile,
            'artisanService' => $artisanService,
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'experience' => ['required', 'integer', 'min:0', 'max:80'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $profile = Art::where('user_id', Auth::id())
            ->whereNull('title')
            ->first();

        if (!$profile) {
            $profile = new Art([
                'user_id' => Auth::id(),
            ]);
        }

        $artisanService = Auth::user()->artisan?->service_type;

        if ($request->hasFile('photo')) {
            $profile->photo = $request->file('photo')->store('avatars', 'public');
        }

        $profile->user_id = Auth::id();
        $profile->metier = $artisanService ?: $profile->metier;
        $profile->experience = (int) $request->experience;
        $profile->save();

        return back()->with('success', 'Profil enregistre avec succes.');
    }

    public function getPortfolio()
    {
        $portfolio = Art::where('user_id', Auth::id())
            ->whereNotNull('title')
            ->latest()
            ->get();

        return response()->json($portfolio->map(fn (Art $art) => $this->transformPortfolioItem($art)));
    }

    public function savePortfolio(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:8192',
        ]);

        $profile = Art::where('user_id', Auth::id())
            ->whereNull('title')
            ->first();

        $art = new Art();
        $art->user_id = Auth::id();
        $art->title = $request->title;
        $art->description = $request->description;
        $art->metier = Auth::user()->artisan?->service_type ?? 'Service artisan';
        $art->experience = $profile?->experience ?? 0;

        if ($request->hasFile('photo')) {
            $processor = new PortfolioImageProcessor();
            $art->photo = $processor->storeWatermarkedImage($request->file('photo'), Auth::user()->name);
        }

        $art->save();

        return response()->json([
            'success' => true,
            'art' => $this->transformPortfolioItem($art),
        ]);
    }

    public function deletePortfolio($id)
    {
        $art = Art::where('user_id', Auth::id())
            ->whereNotNull('title')
            ->findOrFail($id);

        if ($art->photo) {
            if (Storage::disk('local')->exists($art->photo)) {
                Storage::disk('local')->delete($art->photo);
            } elseif (Storage::disk('public')->exists($art->photo)) {
                Storage::disk('public')->delete($art->photo);
            }
        }

        $art->delete();

        return response()->json(['success' => true]);
    }

    public function showPortfolioImage(Art $art)
    {
        abort_if(blank($art->photo), 404);

        $disk = Storage::disk('local')->exists($art->photo) ? 'local' : (Storage::disk('public')->exists($art->photo) ? 'public' : null);
        abort_if($disk === null, 404);

        return response()->file(Storage::disk($disk)->path($art->photo), [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'inline; filename="realisation.jpg"',
            'Cache-Control' => 'private, max-age=3600',
            'X-Robots-Tag' => 'noindex, noimageindex',
        ]);
    }

    public function getWorks($id)
    {
        $artisan = Artisan::find($id);

        if (!$artisan) {
            $query = Artisan::query();

            if ($name = request()->query('name')) {
                $query->where('name', $name);
            }

            if ($serviceType = request()->query('service_type')) {
                $query->where('service_type', $serviceType);
            }

            $commune = request()->query('commune');
            $city = request()->query('city');

            if ($commune || $city) {
                $query->where(function ($builder) use ($commune, $city) {
                    if ($commune) {
                        $builder->orWhere('commune', $commune);
                    }

                    if ($city) {
                        $builder->orWhere('city', $city);
                    }
                });
            }

            $artisan = $query->first();
        }

        if (!$artisan) {
            return response()->json([], 404);
        }

        $works = Art::where('user_id', $artisan->user_id)
            ->whereNotNull('title')
            ->latest()
            ->get();

        return response()->json($works->map(fn (Art $art) => $this->transformPortfolioItem($art)));
    }

    public function searchByJob($job)
    {
        return Artisan::where('service_type', 'LIKE', "%{$job}%")->get();
    }

    public function searchByWilaya($wilaya)
    {
        return Artisan::where('city', $wilaya)->get();
    }

    public function searchByName(Request $request)
    {
        $name = $request->query('name');
        $serviceType = $request->query('service_type');
        $commune = $request->query('commune');
        $city = $request->query('city');
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $minRating = $request->query('min_rating');
        $maxRating = $request->query('max_rating');

        $query = Artisan::query();

        if ($name) {
            $query->where('name', 'LIKE', "%{$name}%");
        }

        if ($serviceType) {
            $query->where('service_type', 'LIKE', "%{$serviceType}%");
        }

        if ($commune || $city) {
            $query->where(function ($builder) use ($commune, $city) {
                if ($commune) {
                    $builder->orWhere('commune', $commune);
                }

                if ($city) {
                    $builder->orWhere('city', $city);
                }
            });
        }

        if (Schema::hasColumn('artisans', 'price')) {
            if ($minPrice !== null) {
                $query->where('price', '>=', (float) $minPrice);
            }

            if ($maxPrice !== null) {
                $query->where('price', '<=', (float) $maxPrice);
            }
        }

        if (Schema::hasColumn('artisans', 'rating')) {
            if ($minRating !== null) {
                $query->where('rating', '>=', (float) $minRating);
            }

            if ($maxRating !== null) {
                $query->where('rating', '<=', (float) $maxRating);
            }
        }

        return $query->get();
    }

    protected function transformPortfolioItem(Art $art): array
    {
        return [
            'id' => $art->id,
            'user_id' => $art->user_id,
            'metier' => $art->metier,
            'experience' => $art->experience,
            'photo' => $art->photo,
            'photoUrl' => $art->photo ? route('artisan.portfolio.image', $art) : null,
            'title' => $art->title,
            'description' => $art->description,
            'artisanName' => $art->user?->name,
        ];
    }
}
