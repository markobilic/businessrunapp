<?php

use Illuminate\Support\Facades\Route;
use Spatie\WelcomeNotification\WelcomesNewUsers;
use App\Http\Controllers\Auth\MyWelcomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

Route::group(['middleware' => ['web', WelcomesNewUsers::class,]], function () {
    Route::get('welcome/{user}', [MyWelcomeController::class, 'showWelcomeForm'])->name('welcome');
    Route::post('welcome/{user}', [MyWelcomeController::class, 'savePassword']);
});

Route::group(['middleware' => ['auth', 'verified']], function() {
    Route::view('/', 'dashboard');

    Route::view('dashboard', 'dashboard')
        ->name('dashboard');
        
    Route::view('profile', 'profile')
        ->name('profile');

    Route::view('company', 'company')
        ->name('company');
});

Route::view('microsite/{reservationHash}', 'microsite')->name('microsite');

Route::prefix('races')->name('races.')->middleware(['auth', 'verified'])->group(function() {
    Route::view('/', 'races')->middleware(['role:superadmin|organizer|collaborator|partner|captain'])->name('list');
    Route::view('/create', 'create-race')->middleware(['role:superadmin|organizer|partner'])->name('create');
    Route::view('/edit/{raceId}', 'edit-race')->middleware(['role:superadmin|organizer'])->name('edit');
    Route::view('/{raceId}', 'show-race')->middleware(['role:superadmin|organizer|collaborator|partner|captain'])->name('show');
    Route::view('/start-list/{raceId}', 'start-list')->middleware(['role:superadmin|organizer|collaborator|partner'])->name('start-list');
    Route::view('/financial-report/{raceId}', 'financial-report')->middleware(['role:superadmin|organizer|partner'])->name('financial-report');
});

Route::prefix('teams')->name('teams.')->middleware(['auth', 'verified'])->group(function() {
    Route::view('/', 'teams')->middleware(['role:superadmin|organizer|collaborator|partner'])->name('list');
    Route::view('/create', 'create-team')->middleware(['role:superadmin|organizer|partner'])->name('create');
    Route::view('/edit/{teamId}', 'edit-team')->middleware(['role:superadmin|organizer'])->name('edit');
    Route::view('/{teamId}', 'show-team')->middleware(['role:superadmin|organizer|collaborator|partner'])->name('show');
    Route::view('/addresses/{teamId}/edit/{addressId}', 'address-edit')->middleware(['role:superadmin|organizer'])->name('address-edit');
    Route::view('/addresses/{teamId}/create', 'address-create')->middleware(['role:superadmin|organizer'])->name('address-create');
});

Route::prefix('runners')->name('runners.')->middleware(['auth', 'verified'])->group(function() {
    Route::view('/', 'runners')->middleware(['role:superadmin|organizer|collaborator|partner|captain'])->name('list');
    Route::view('/create', 'create-runner')->middleware(['role:superadmin|organizer|partner|captain'])->name('create');
    Route::view('/edit/{runnerId}', 'edit-runner')->middleware(['role:superadmin|organizer|captain'])->name('edit');
    Route::view('/{runnerId}', 'show-runner')->middleware(['role:superadmin|organizer|collaborator|partner|captain'])->name('show');
});

Route::prefix('reservations')->name('reservations.')->middleware(['auth', 'verified'])->group(function() {
    Route::view('/', 'reservations')->middleware(['role:superadmin|organizer|collaborator|partner|captain'])->name('list');
    Route::view('/create/{raceId?}', 'create-reservation')->middleware(['role:superadmin|organizer|partner|captain'])->name('create');
    Route::view('/edit/{reservationId}', 'edit-reservation')->middleware(['role:superadmin|organizer|captain'])->name('edit');
    Route::view('/{reservationId}', 'show-reservation')->middleware(['role:superadmin|organizer|collaborator|partner|captain'])->name('show');
});

Route::prefix('analytics')->name('analytics.')->middleware(['auth', 'verified'])->group(function() {
    Route::view('/', 'analytics')->middleware(['role:superadmin|organizer'])->name('show');
});

Route::prefix('settings')->name('settings.')->middleware(['auth', 'verified'])->group(function() {
    Route::view('/', 'settings')->middleware(['role:superadmin|organizer'])->name('show');
});

Route::prefix('transactions')->name('transactions.')->middleware(['auth', 'verified'])->group(function() {
    Route::view('/', 'transactions')->middleware(['role:superadmin|organizer'])->name('list');
});

Route::view('public/runners/{year?}/{raceId?}/{captainId?}', 'all-runners')->name('all-runners');
Route::view('public/captains/{year?}/{raceId?}', 'all-captains')->name('all-captains');

require __DIR__.'/auth.php';

Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->name('logout');
