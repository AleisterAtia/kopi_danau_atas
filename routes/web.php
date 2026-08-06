<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TourPackageController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tentang-kami', [AboutController::class, 'index'])->name('about');
Route::get('/paket-wisata', [TourPackageController::class, 'index'])->name('packages.index');
Route::get('/paket-wisata/{slug}', [TourPackageController::class, 'show'])->name('packages.show');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/lang/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [LoginController::class, 'showForm'])->name('login');
    Route::post('/masuk', [LoginController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/daftar', [RegisterController::class, 'showForm'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'register'])->middleware('throttle:5,1');

    // Google Login Routes
    Route::get('/auth/google/redirect', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
    Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

    // Password reset
    Route::get('/lupa-password', [ForgotPasswordController::class, 'showForm'])->name('password.request');
    Route::post('/lupa-password', [ForgotPasswordController::class, 'sendLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'update'])->middleware('throttle:5,1')->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Email Verification Routes
|--------------------------------------------------------------------------
*/

Route::get('/email/verify', function () {
    return view('pages.auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/')->with('success', __('Email Anda berhasil diverifikasi.'));
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', __('Link verifikasi telah dikirim ulang!'));
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

// Used by the admin panel's "Aktifkan Notifikasi" button — kept outside the
// 'verified' group below since it gates on Filament's own role check, not
// public-site email verification.
Route::middleware('auth')->group(function () {
    Route::post('/push-subscription', [PushSubscriptionController::class, 'store'])->name('push-subscription.store');
    Route::delete('/push-subscription', [PushSubscriptionController::class, 'destroy'])->name('push-subscription.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Booking
    Route::get('/booking', [BookingController::class, 'myBookings'])->name('booking.index');
    Route::post('/booking/create', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/booking/{booking}', [BookingController::class, 'show'])->name('booking.show');
    Route::post('/booking/{booking}/cancel', [BookingController::class, 'cancel'])->name('booking.cancel');
    Route::get('/booking/{booking}/invoice', [InvoiceController::class, 'download'])->name('booking.invoice');

    // Payment
    Route::get('/booking/{booking}/bayar', [PaymentController::class, 'checkout'])->name('payment.checkout');
    Route::post('/booking/{booking}/pay', [PaymentController::class, 'createSnapToken'])->name('payment.snap');
    Route::post('/booking/{booking}/update-status', [PaymentController::class, 'updateStatus'])->name('payment.update-status');

    // Review
    Route::post('/booking/{booking}/review', [ReviewController::class, 'store'])->name('review.store');

    // Profile
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
});

/*
|--------------------------------------------------------------------------
| API Routes (no CSRF)
|--------------------------------------------------------------------------
*/

Route::get('/api/kuota/{packageId}/{date}', [BookingController::class, 'checkQuota'])->name('api.quota');
