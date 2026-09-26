<?php

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'home'])->name('home');
Route::get('/plastic', [CatalogController::class, 'plastic'])->name('catalog.plastic');
Route::get('/hardware', [CatalogController::class, 'hardware'])->name('catalog.hardware');
Route::get('/search', [CatalogController::class, 'search'])->name('catalog.search');
Route::get('/product/{slug}', [CatalogController::class, 'show'])->name('catalog.show');
Route::get('/contacts', ContactController::class)->name('contacts');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/cabinet', DashboardController::class)->name('dashboard');
    Route::get('/orders/{order}', [DashboardController::class, 'show'])->name('orders.show');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{product}', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{product}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::resource('clients', ClientController::class)->except(['show', 'destroy']);
});

Route::get('/locale/{locale}', function (string $locale) {
    if (! in_array($locale, ['ru', 'ro'], true)) {
        abort(404);
    }
    session(['locale' => $locale]);

    return back();
})->name('locale.switch');
