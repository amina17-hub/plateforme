<?php

namespace App\Http\Controllers;

use App\Mail\HomeContactMessageMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class HomeContactController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $recipientEmail = (string) config('services.contact.recipient_email', config('mail.from.address'));

        if ($recipientEmail === '') {
            Log::warning('Home contact email recipient is not configured.');

            return response()->json([
                'success' => false,
                'message' => 'Le service de contact est momentanement indisponible.',
            ], 500);
        }

        try {
            Mail::to($recipientEmail)->send(new HomeContactMessageMail(
                senderName: $validated['name'],
                senderEmail: $validated['email'],
                messageBody: $validated['message'],
            ));
        } catch (\Throwable $exception) {
            Log::warning('Home contact email could not be sent.', [
                'recipient_email' => $recipientEmail,
                'sender_email' => $validated['email'],
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Le message n\'a pas pu etre envoye. Merci de reessayer.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Message envoye avec succes.',
        ]);
    }
}
