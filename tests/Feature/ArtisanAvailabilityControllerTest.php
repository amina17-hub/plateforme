<?php

namespace Tests\Feature;

use App\Models\Artisan;
use App\Models\ArtisanAvailability;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ArtisanAvailabilityControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_artisan_can_mark_a_future_day_as_available(): void
    {
        $artisanUser = User::create([
            'name' => 'Artisan Test',
            'email' => 'artisan-availability@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'artisan',
        ]);

        $artisan = Artisan::create([
            'user_id' => $artisanUser->id,
            'name' => 'Artisan Test',
            'service_type' => 'Plomberie',
            'city' => 'Skikda',
            'commune' => 'Skikda',
        ]);

        $response = $this->actingAs($artisanUser)->putJson(route('artisan.availability.upsert'), [
            'available_date' => now()->addDays(2)->toDateString(),
            'is_available' => true,
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('artisan_availabilities', [
            'artisan_id' => $artisan->id,
            'available_date' => now()->addDays(2)->toDateString(),
            'is_available' => true,
        ]);
    }

    public function test_client_can_only_book_an_available_day(): void
    {
        $artisanUser = User::create([
            'name' => 'Artisan Reservation',
            'email' => 'artisan-reservation@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'artisan',
        ]);

        $clientUser = User::create([
            'name' => 'Client Reservation',
            'email' => 'client-reservation@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'client',
        ]);

        $artisan = Artisan::create([
            'user_id' => $artisanUser->id,
            'name' => 'Artisan Reservation',
            'service_type' => 'Electricite',
            'city' => 'Skikda',
            'commune' => 'Skikda',
        ]);

        ArtisanAvailability::create([
            'artisan_id' => $artisan->id,
            'available_date' => now()->addDays(3)->toDateString(),
            'is_available' => false,
        ]);

        $unavailableResponse = $this->actingAs($clientUser)->postJson(route('client.reservations.store'), [
            'artisan_id' => $artisan->id,
            'reservation_date' => now()->addDays(3)->toDateString(),
            'reservation_time' => '10:00',
            'notes' => 'Intervention rapide',
        ]);

        $unavailableResponse
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Cette date n\'est pas disponible chez cet artisan.',
            ]);

        ArtisanAvailability::updateOrCreate(
            [
                'artisan_id' => $artisan->id,
                'available_date' => now()->addDays(4)->toDateString(),
            ],
            [
                'is_available' => true,
            ]
        );

        $availableResponse = $this->actingAs($clientUser)->postJson(route('client.reservations.store'), [
            'artisan_id' => $artisan->id,
            'reservation_date' => now()->addDays(4)->toDateString(),
            'reservation_time' => '11:00',
            'notes' => 'Deuxieme intervention',
        ]);

        $availableResponse
            ->assertCreated()
            ->assertJsonPath('reservationDate', now()->addDays(4)->toDateString());
    }

    public function test_client_can_only_book_the_same_artisan_once_per_day(): void
    {
        $artisanUser = User::create([
            'name' => 'Artisan Unique',
            'email' => 'artisan-unique@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'artisan',
        ]);

        $clientUser = User::create([
            'name' => 'Client Unique',
            'email' => 'client-unique@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'client',
        ]);

        $artisan = Artisan::create([
            'user_id' => $artisanUser->id,
            'name' => 'Artisan Unique',
            'service_type' => 'Plomberie',
            'city' => 'Skikda',
            'commune' => 'Skikda',
        ]);

        $reservationDate = now()->addDays(5)->toDateString();

        $this->actingAs($clientUser)->postJson(route('client.reservations.store'), [
            'artisan_id' => $artisan->id,
            'reservation_date' => $reservationDate,
            'reservation_time' => '09:00',
        ])->assertCreated();

        $this->actingAs($clientUser)->postJson(route('client.reservations.store'), [
            'artisan_id' => $artisan->id,
            'reservation_date' => $reservationDate,
            'reservation_time' => '15:00',
        ])
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Vous avez deja reserve cet artisan pour cette date.',
            ]);

        $this->assertDatabaseCount('reservations', 1);
    }
}
