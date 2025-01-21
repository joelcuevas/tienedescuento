<?php

use App\Http\Middleware\StoreIntendedUrl;
use App\Livewire\Admin\ShowTracking;
use App\Livewire\NotFoundPage;
use App\Livewire\Web\SearchProducts;
use App\Livewire\Web\ShowCatalog;
use App\Livewire\Web\ShowHome;
use App\Livewire\Web\ShowProduct;
use App\Livewire\Web\ShowStores;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect(config('params.default_country')));
Route::get('/intended', fn () => redirect(session('app.intended', '/')));

Route::middleware(
    [
        'auth:sanctum',
        'verified',
        config('jetstream.auth_session'),
    ])
    ->prefix('user')
    ->group(function () {
        Route::get('/products', ShowTracking::class)->name('user.products');
    });

Route::middleware(
    [
        StoreIntendedUrl::class,
    ])
    ->prefix('{countryCode}')
    ->whereIn('countryCode', array_keys(config('params.countries')))
    ->group(function () {
        Route::get('/404', NotFoundPage::class)->name('404');

        Route::get('/', ShowHome::class)->name('home');

        Route::get('/stores', ShowStores::class)->name('stores.index');
        Route::get('/search', SearchProducts::class)->name('products.search');

        Route::get('/{storeSlug}/p/{productSku}/{productSlug}', ShowProduct::class)->name('products.show');

        Route::get('/{catalogSlug}/b/{brandSlug}', ShowCatalog::class)->name('catalogs.brand');
        Route::get('/{catalogSlug}/c/{categorySlug}', ShowCatalog::class)->name('catalogs.category');
        Route::get('/{catalogSlug}/c/{categorySlug}/b/{brandSlug}', ShowCatalog::class)->name('catalogs.category_brand');
        Route::get('/{catalogSlug}/{taxonomySlug?}', ShowCatalog::class)->name('catalogs.index');
    });

require __DIR__.'/socialstream.php';
