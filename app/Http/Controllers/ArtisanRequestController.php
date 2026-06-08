<?php

namespace App\Http\Controllers;

use App\Models\Artisan;
use App\Models\User;
use App\Notifications\ArtisanRequestNotification;
use App\Notifications\ClientRequestStatusNotification;
use Illuminate\Http\Request;

class ArtisanRequestController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'artisan_id' => ['required', 'exists:artisans,id'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        $artisan = Artisan::findOrFail($validated['artisan_id']);
        abort_unless($artisan->user, 422, 'Cet artisan n\'a pas encore de compte utilisateur lie.');

        $payload = [
            'client_user_id' => $request->user()->id,
            'client_name' => $request->user()->name,
            'artisan_id' => $artisan->id,
            'artisan_name' => $artisan->name,
            'service_type' => $artisan->service_type ?: 'Service artisan',
            'message' => $validated['message'],
        ];

        $artisan->user->notify(new ArtisanRequestNotification($payload));

        $notification = $artisan->user
            ->notifications()
            ->where('type', ArtisanRequestNotification::class)
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'notificationId' => $notification?->id,
            'clientUserId' => $request->user()->id,
        ], 201);
    }

    public function updateStatus(Request $request)
    {
        $validated = $request->validate([
            'client_user_id' => ['required', 'exists:users,id'],
            'artisan_name' => ['required', 'string', 'max:255'],
            'service_type' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:acceptee,refusee'],
            'notification_id' => ['nullable', 'string'],
        ]);

        if (!empty($validated['notification_id'])) {
            $notification = $request->user()->notifications()->find($validated['notification_id']);

            if ($notification) {
                $data = $notification->data;
                $data['status'] = $validated['status'];
                $notification->update([
                    'data' => $data,
                    'read_at' => now(),
                ]);
            }
        }

        User::findOrFail($validated['client_user_id'])
            ->notify(new ClientRequestStatusNotification($validated, $validated['status']));

        return response()->json(['success' => true]);
    }
}
