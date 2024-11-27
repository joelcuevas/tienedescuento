<?php

use App\Http\Middleware\SetCountryCode;
use App\Livewire\Web\ShowHome;
use App\Livewire\Web\ShowProduct;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect(config('params.default_country')));

Route::middleware(['auth:sanctum', 'verified', config('jetstream.auth_session')])
    ->group(function () {
        Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
    });

Route::middleware(SetCountryCode::class)->prefix('{countryCode}')
    ->group(function () {
        Route::get('/', ShowHome::class);
        Route::get('/{storeSlug}/p/{productSku}/{productSlug}', ShowProduct::class)->name('products.show');
    });

require __DIR__.'/socialstream.php';
