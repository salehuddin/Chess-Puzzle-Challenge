<?php

use App\Http\Controllers\Admin\EditorJsUploadController;
use App\Http\Controllers\BundleEnrollmentController;
use App\Http\Controllers\ChallengeEnrollmentController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DocController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicProfileController;
use App\Livewire\ChallengeIndex;
use App\Livewire\ChallengeShow;
use App\Livewire\Dashboard;
use App\Livewire\EnrolledChallenge;
use App\Livewire\HallOfFame;
use App\Livewire\MedalRequest;
use App\Livewire\PuzzlePlayer;
use App\Models\Bundle;
use App\Models\Challenge;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $challenges = Challenge::active()->withCount('puzzles')->take(3)->get();
    $bundles = Bundle::active()->with('challenges')->take(2)->get();

    return view('welcome', compact('challenges', 'bundles'));
});

Route::get('/new', function () {
    $challenges = Challenge::active()->withCount('puzzles')->take(6)->get();
    $bundles = Bundle::active()->with('challenges')->take(3)->get();

    return view('landing.welcome-v2', compact('challenges', 'bundles'));
})->name('landing.new');

Route::get('/challenges', ChallengeIndex::class)->name('challenges.index');
Route::get('/challenges/{challenge:slug}', ChallengeShow::class)->name('challenges.show');
Route::get('/challenges/{challenge:slug}/enroll', ChallengeEnrollmentController::class)->name('challenges.enroll');
Route::get('/bundles/{bundle:slug}/enroll', BundleEnrollmentController::class)->name('bundles.enroll');
Route::view('/mockups/single-challenge', 'mockups.single-challenge')->name('mockups.single-challenge');

Route::get('/docs', [DocController::class, 'index'])->name('docs.index');
Route::get('/docs/{path}', [DocController::class, 'show'])->where('path', '.+')->name('docs.show');

Route::get('/dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/u/{user:username}', [PublicProfileController::class, 'show'])->name('profile.show');

Route::middleware('auth')->group(function () {
    Route::get('/checkout/{order}', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/{order}/pay', [CheckoutController::class, 'pay'])->name('checkout.pay');

    Route::get('/play/{enrollment}', PuzzlePlayer::class)->name('play');
    Route::get('/enrollments/{enrollment}', EnrolledChallenge::class)->name('enrollments.show');
    Route::get('/medal-request/{enrollment}', MedalRequest::class)->name('medal-request');
    Route::get('/hall-of-fame', HallOfFame::class)->name('hall-of-fame');
    Route::get('/orders/{enrollment}', fn (Enrollment $enrollment) => redirect()->route('enrollments.show', $enrollment, 301))->name('orders.track');

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
