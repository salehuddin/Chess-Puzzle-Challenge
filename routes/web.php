<?php

use App\Http\Controllers\Admin\EditorJsUploadController;
use App\Http\Controllers\BundleEnrollmentController;
use App\Http\Controllers\ChallengeEnrollmentController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProfileController;
use App\Livewire\ChallengeIndex;
use App\Livewire\ChallengeShow;
use App\Livewire\Dashboard;
use App\Livewire\HallOfFame;
use App\Livewire\MedalRequest;
use App\Livewire\OrderTracking;
use App\Livewire\PuzzlePlayer;
use App\Models\Bundle;
use App\Models\Challenge;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $challenges = Challenge::active()->withCount('puzzles')->take(3)->get();
    $bundles = Bundle::active()->with('challenges')->take(2)->get();

    return view('welcome', compact('challenges', 'bundles'));
});

Route::get('/challenges', ChallengeIndex::class)->name('challenges.index');
Route::get('/challenges/{challenge:slug}', ChallengeShow::class)->name('challenges.show');
Route::get('/challenges/{challenge:slug}/enroll', ChallengeEnrollmentController::class)->name('challenges.enroll');
Route::get('/bundles/{bundle:slug}/enroll', BundleEnrollmentController::class)->name('bundles.enroll');
Route::view('/mockups/single-challenge', 'mockups.single-challenge')->name('mockups.single-challenge');

Route::get('/dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/checkout/{order}', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/{order}/pay', [CheckoutController::class, 'pay'])->name('checkout.pay');

    Route::get('/play/{enrollment}', PuzzlePlayer::class)->name('play');
    Route::get('/medal-request/{enrollment}', MedalRequest::class)->name('medal-request');
    Route::get('/hall-of-fame', HallOfFame::class)->name('hall-of-fame');
    Route::get('/orders/{enrollment}', OrderTracking::class)->name('orders.track');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'staff'])->prefix('admin/editorjs')->group(function () {
    Route::post('/image', [EditorJsUploadController::class, 'storeImage']);
    Route::post('/image-by-url', [EditorJsUploadController::class, 'storeImageUrl']);
    Route::post('/attaches', [EditorJsUploadController::class, 'storeAttaches']);
});

require __DIR__.'/auth.php';
