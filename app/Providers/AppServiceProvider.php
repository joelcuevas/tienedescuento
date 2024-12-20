<?php

namespace App\Providers;

use App\Http\Middleware\SetCountryCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $locale = config('app.locale');

        Carbon::setLocale($locale);
        setlocale(LC_TIME, $locale);
        //DB::statement("SET lc_time_names = '{$locale}'");

        Model::unguard();

        Livewire::addPersistentMiddleware([SetCountryCode::class]);
    }
}
