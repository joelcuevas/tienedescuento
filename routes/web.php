<?php

use App\Http\Middleware\SetCountryCode;
use App\Http\Middleware\StoreIntendedUrl;
use App\Livewire\Web\SearchProduct;
use App\Livewire\Web\ShowCatalog;
use App\Livewire\Web\ShowHome;
use App\Livewire\Web\ShowProduct;
use App\Livewire\Web\ShowStores;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect(config('params.default_country')));
Route::get('/intended', fn() => redirect(session('app.intended', '/')));

Route::middleware([
        'auth:sanctum', 
        'verified', 
        config('jetstream.auth_session'),
    ])
    ->group(function () {
        Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
    });

Route::middleware([
        StoreIntendedUrl::class,
    ])
    ->prefix('{countryCode}')
    ->group(function () {
        Route::get('/', ShowHome::class)->name('home');

        Route::get('/stores', ShowStores::class)->name('stores.index');
        Route::get('/search', SearchProduct::class)->name('products.search');

        Route::get('/{storeSlug}', ShowCatalog::class)->name('catalogs.store');
        Route::get('/{storeSlug}/b/{brandSlug}', ShowCatalog::class)->name('catalogs.brand');
        Route::get('/{storeSlug}/c/{categorySlug}', ShowCatalog::class)->name('catalogs.category');
        Route::get('/{storeSlug}/p/{productSku}/{productSlug}', ShowProduct::class)->name('products.show');
    });

require __DIR__.'/socialstream.php';
