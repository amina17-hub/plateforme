<?php

namespace App\Http\Controllers;

use App\Models\Artisan;
use App\Models\ArtisanAvailability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArtisanAvailabilityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $artisan = $request->user()->artisan;
        abort_unless($artisan, 404);

        return response()->json($this->getAvailabilityPayload($artisan));
    }

    public function show(Artisan $artisan): JsonResponse
    {
        return response()->json($this->getAvailabilityPayload($artisan));
    }

    public function upsert(Request $request): JsonResponse
    {
        $artisan = $request->user()->artisan;
        abort_unless($artisan, 404);

        $validated = $request->validate([
            'available_date' => ['required', 'date', 'after_or_equal:today'],
            'is_available' => ['required', 'boolean'],
        ]);

        if ((bool) $validated['is_available']) {
            ArtisanAvailability::query()
                ->where('artisan_id', $artisan->id)
                ->whereDate('available_date', $validated['available_date'])
                ->delete();

            return response()->json([
                'success' => true,
                'availability' => [
                    'id' => null,
                    'artisanId' => $artisan->id,
                    'availableDate' => $validated['available_date'],
                    'isAvailable' => true,
                ],
            ]);
        }

        $availability = ArtisanAvailability::updateOrCreate(
            [
                'artisan_id' => $artisan->id,
                'available_date' => $validated['available_date'],
            ],
            [
                'is_available' => $validated['is_available'],
            ]
        );

        return response()->json([
            'success' => true,
            'availability' => $this->transformAvailability($availability),
        ]);
    }

    protected function getAvailabilityPayload(Artisan $artisan): array
    {
        $availabilities = $artisan->availabilities()
            ->whereDate('available_date', '>=', now()->toDateString())
            ->orderBy('available_date')
            ->get();

        return $availabilities
            ->map(fn (ArtisanAvailability $availability) => $this->transformAvailability($availability))
            ->values()
            ->all();
    }

    protected function transformAvailability(ArtisanAvailability $availability): array
    {
        return [
            'id' => $availability->id,
            'artisanId' => $availability->artisan_id,
            'availableDate' => optional($availability->available_date)->format('Y-m-d'),
            'isAvailable' => (bool) $availability->is_available,
        ];
    }
}
