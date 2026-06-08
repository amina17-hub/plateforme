<?php

namespace App\Http\Controllers;

use App\Models\Artisan;
use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function show(Request $request, Artisan $artisan)
    {
        $client = $request->user()->client;

        $currentRating = null;

        if ($client) {
            $currentRating = Rating::query()
                ->where('artisan_id', $artisan->id)
                ->where('client_id', $client->id)
                ->first();
        }

        return response()->json([
            'averageRating' => $this->averageRating($artisan),
            'ratingsCount' => Rating::query()->where('artisan_id', $artisan->id)->count(),
            'currentRating' => $currentRating ? (float) $currentRating->rating : null,
            'currentComment' => $currentRating?->comment ?? '',
        ]);
    }

    public function store(Request $request, Artisan $artisan)
    {
        $client = $request->user()->client;

        abort_unless($client, 403, 'Seuls les clients peuvent noter un artisan.');

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        Rating::updateOrCreate(
            [
                'client_id' => $client->id,
                'artisan_id' => $artisan->id,
            ],
            [
                'rating' => $validated['rating'],
                'comment' => trim((string) ($validated['comment'] ?? '')),
            ]
        );

        $artisan->rating = $this->averageRating($artisan);
        $artisan->save();

        return $this->show($request, $artisan->fresh());
    }

    protected function averageRating(Artisan $artisan): float
    {
        return round((float) Rating::query()
            ->where('artisan_id', $artisan->id)
            ->avg('rating'), 1);
    }
}
