<?php

namespace Tests\Feature;

use App\Models\Art;
use App\Models\Artisan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArtisanPortfolioProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_portfolio_upload_returns_protected_image_url(): void
    {
        Storage::fake('local');

        $artisanUser = User::create([
            'name' => 'Artisan Protege',
            'email' => 'artisan-protege@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'artisan',
        ]);

        Artisan::create([
            'user_id' => $artisanUser->id,
            'name' => 'Artisan Protege',
            'service_type' => 'Peinture',
            'city' => 'Skikda',
            'commune' => 'Skikda',
        ]);

        $response = $this->actingAs($artisanUser)->post(route('artisan.portfolio.save'), [
            'title' => 'Mur repeint',
            'description' => 'Avant apres du chantier.',
            'photo' => UploadedFile::fake()->image('chantier.jpg', 1600, 1200),
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $art = Art::where('user_id', $artisanUser->id)->where('title', 'Mur repeint')->firstOrFail();

        Storage::disk('local')->assertExists($art->photo);
        $this->assertSame(route('artisan.portfolio.image', $art), $response->json('art.photoUrl'));
    }

    public function test_portfolio_image_route_streams_image_inline(): void
    {
        Storage::fake('local');

        $artisanUser = User::create([
            'name' => 'Artisan Image',
            'email' => 'artisan-image@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'artisan',
        ]);

        $art = Art::create([
            'user_id' => $artisanUser->id,
            'metier' => 'Carrelage',
            'experience' => 4,
            'photo' => 'portfolio/test.jpg',
            'title' => 'Salle de bain',
            'description' => 'Pose complete.',
        ]);

        Storage::disk('local')->put('portfolio/test.jpg', UploadedFile::fake()->image('test.jpg', 50, 50)->get());

        $response = $this->get(route('artisan.portfolio.image', $art));

        $response->assertOk();
        $response->assertHeader('Content-Disposition', 'inline; filename="realisation.jpg"');
    }
}
