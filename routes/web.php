<?php

use App\Http\Controllers\ArtisanController;
use App\Http\Controllers\ArtisanAvailabilityController;
use App\Http\Controllers\ArtisanRequestController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\HomeContactController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\Public\MetierController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\RegisterController;
use App\Models\Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [MetierController::class, 'index'])->name('welcome');
Route::get('/home', [MetierController::class, 'index'])->name('home');
Route::get('/search-metiers', [MetierController::class, 'search'])->name('metiers.search');
Route::post('/home/contact', [HomeContactController::class, 'send'])->name('home.contact');
Route::get('/portfolio/image/{art}', [ArtisanController::class, 'showPortfolioImage'])->name('artisan.portfolio.image');

Route::post('/register', [RegisterController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.store');
Route::get('/reset-password-success', [PasswordResetController::class, 'success'])->name('password.reset.success');
Route::get('/artisans/{artisan}/availability', [ArtisanAvailabilityController::class, 'show'])->name('artisans.availability.show');

Route::middleware('auth')->group(function () {
    Route::view('/admin', 'admin.dashboard')->name('admin.dashboard');
    Route::get('/artisan', [ArtisanController::class, 'dashboard'])->name('artisan.dashboard');
    Route::get('/client', [ClientController::class, 'dashboard'])->name('client.dashboard');

    Route::post('/artisan/upload', [ArtisanController::class, 'upload'])->name('artisan.upload');
    Route::get('/artisan/portfolio', [ArtisanController::class, 'getPortfolio'])->name('artisan.portfolio.get');
    Route::post('/artisan/portfolio', [ArtisanController::class, 'savePortfolio'])->name('artisan.portfolio.save');
    Route::delete('/artisan/portfolio/{id}', [ArtisanController::class, 'deletePortfolio'])->name('artisan.portfolio.delete');
    Route::get('/artisan/works/{id}', [ArtisanController::class, 'getWorks'])->name('artisan.works.get');
    Route::get('/artisan/reservations', [ReservationController::class, 'artisanIndex'])->name('artisan.reservations.index');
    Route::patch('/artisan/reservations/{reservation}/status', [ReservationController::class, 'updateStatus'])->name('artisan.reservations.status');
    Route::get('/artisan/availability', [ArtisanAvailabilityController::class, 'index'])->name('artisan.availability.index');
    Route::put('/artisan/availability', [ArtisanAvailabilityController::class, 'upsert'])->name('artisan.availability.upsert');
    Route::get('/artisan/recommend', [RecommendationController::class, 'recommend'])
        ->name('artisan.recommend');
    Route::get('/client/reservations', [ReservationController::class, 'clientIndex'])->name('client.reservations.index');
    Route::post('/client/reservations', [ReservationController::class, 'store'])->name('client.reservations.store');
    Route::delete('/client/reservations/{reservation}', [ReservationController::class, 'destroy'])->name('client.reservations.destroy');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/client/artisan-requests', [ArtisanRequestController::class, 'store'])->name('client.artisan-requests.store');
    Route::patch('/artisan/client-requests/status', [ArtisanRequestController::class, 'updateStatus'])->name('artisan.client-requests.status');
    Route::get('/artisans/{artisan}/ratings', [RatingController::class, 'show'])->name('artisans.ratings.show');
    Route::post('/artisans/{artisan}/ratings', [RatingController::class, 'store'])->name('artisans.ratings.store');

    Route::post('/logout', function () {
        Auth::logout();

        return redirect()->route('welcome');
    })->name('logout');
});

Route::get('/artisans', [ArtisanController::class, 'index']);
Route::get('/artisans/{id}', [ArtisanController::class, 'show']);
Route::get('/search/job/{job}', [ArtisanController::class, 'searchByJob']);

Route::get('/artisans/search/name', [ArtisanController::class, 'searchByName'])->name('artisans.search.name');

Route::get('/search/commune/{commune}', function ($commune) {
    return Artisan::where('commune', $commune)->get();
});

Route::get('/search/combo', function (Request $request) {
    $job = $request->query('job');
    $commune = $request->query('commune');

    return Artisan::where('service_type', 'like', "%{$job}%")
        ->where('commune', $commune)
        ->get();
});
