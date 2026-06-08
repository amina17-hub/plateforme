<?php

namespace App\Http\Controllers;

use App\Models\Artisan;
use App\Models\ArtisanAvailability;
use App\Models\Reservation;
use App\Notifications\NewReservationNotification;
use App\Notifications\ReservationStatusNotification;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function clientIndex(Request $request)
    {
        $reservations = Reservation::query()
            ->where('client_user_id', $request->user()->id)
            ->orderBy('reservation_date')
            ->orderBy('reservation_time')
            ->get();

        return response()->json($reservations->map(fn (Reservation $reservation) => $this->transformReservation($reservation)));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'artisan_id' => ['required', 'exists:artisans,id'],
            'reservation_date' => ['required', 'date', 'after_or_equal:today'],
            'reservation_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ]);

        $artisan = Artisan::findOrFail($validated['artisan_id']);

        $isUnavailable = ArtisanAvailability::query()
            ->where('artisan_id', $artisan->id)
            ->whereDate('available_date', $validated['reservation_date'])
            ->where('is_available', false)
            ->exists();

        if ($isUnavailable) {
            return response()->json([
                'message' => 'Cette date n\'est pas disponible chez cet artisan.',
            ], 422);
        }

        $alreadyReserved = Reservation::query()
            ->where('client_user_id', $request->user()->id)
            ->where('artisan_id', $artisan->id)
            ->whereDate('reservation_date', $validated['reservation_date'])
            ->exists();

        if ($alreadyReserved) {
            return response()->json([
                'message' => 'Vous avez deja reserve cet artisan pour cette date.',
            ], 422);
        }

        try {
            $reservation = Reservation::create([
                'client_user_id' => $request->user()->id,
                'artisan_id' => $artisan->id,
                'artisan_user_id' => $artisan->user_id,
                'client_name' => $request->user()->name,
                'artisan_name' => $artisan->name,
                'service_type' => $artisan->service_type,
                'city' => $artisan->commune ?: $artisan->city,
                'quoted_price' => $artisan->price ?? null,
                'reservation_date' => $validated['reservation_date'],
                'reservation_time' => $validated['reservation_time'],
                'notes' => $validated['notes'] ?? '',
                'status' => 'en_attente',
            ]);
        } catch (QueryException $exception) {
            if (in_array((string) $exception->getCode(), ['23000', '23505'], true)) {
                return response()->json([
                    'message' => 'Vous avez deja reserve cet artisan pour cette date.',
                ], 422);
            }

            throw $exception;
        }

        if ($reservation->artisanUser) {
            $reservation->artisanUser->notify(new NewReservationNotification($reservation));
        }

        return response()->json($this->transformReservation($reservation), 201);
    }

    public function destroy(Request $request, Reservation $reservation)
    {
        abort_unless($reservation->client_user_id === $request->user()->id, 403);

        $reservation->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    public function artisanIndex(Request $request)
    {
        $artisan = $request->user()->artisan;
        abort_unless($artisan, 404);

        $reservations = Reservation::query()
            ->where('artisan_id', $artisan->id)
            ->orderBy('reservation_date')
            ->orderBy('reservation_time')
            ->get();

        return response()->json($reservations->map(fn (Reservation $reservation) => $this->transformReservation($reservation)));
    }

    public function updateStatus(Request $request, Reservation $reservation)
    {
        $artisan = $request->user()->artisan;
        abort_unless($artisan && $reservation->artisan_id === $artisan->id, 403);

        $validated = $request->validate([
            'status' => ['required', 'in:en_attente,confirmee,annulee'],
        ]);

        $reservation->update([
            'status' => $validated['status'],
        ]);

        $reservation = $reservation->fresh();

        if ($reservation->clientUser) {
            $reservation->clientUser->notify(new ReservationStatusNotification($reservation));
        }

        return response()->json($this->transformReservation($reservation));
    }

    protected function transformReservation(Reservation $reservation): array
    {
        return [
            'id' => $reservation->id,
            'clientUserId' => $reservation->client_user_id,
            'artisanId' => $reservation->artisan_id,
            'artisanUserId' => $reservation->artisan_user_id,
            'clientName' => $reservation->client_name,
            'artisanName' => $reservation->artisan_name,
            'serviceType' => $reservation->service_type,
            'city' => $reservation->city,
            'price' => $reservation->quoted_price,
            'reservationDate' => optional($reservation->reservation_date)->format('Y-m-d'),
            'reservationTime' => substr((string) $reservation->reservation_time, 0, 5),
            'notes' => $reservation->notes,
            'status' => $reservation->status,
            'createdAt' => optional($reservation->created_at)->toISOString(),
        ];
    }
}
