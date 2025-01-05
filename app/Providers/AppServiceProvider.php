<?php

namespace App\Providers;

use App\Http\Middleware\SetCountryCode;
use App\Support\LimitedPaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Sentry;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $locale = config('app.locale');

        Carbon::setLocale($locale);
        setlocale(LC_TIME, $locale);
        // DB::statement("SET lc_time_names = '{$locale}'");

        Model::unguard();

        Livewire::addPersistentMiddleware([SetCountryCode::class]);

        Builder::macro('paginate', function ($perPage = 36) {
            return LimitedPaginator::fromQuery($this, $perPage, $perPage * 10);
        });

        DB::listen(function ($query) {
            // combine query with its bindings
            $sqlWithBindings = vsprintf(
                str_replace('?', "'%s'", $query->sql),
                array_map('addslashes', $query->bindings),
            );
        
            // add to sentry breadcrumbs
            Sentry\addBreadcrumb(new Sentry\Breadcrumb(
                Sentry\Breadcrumb::LEVEL_INFO,
                'db.sql.query',
                'query.formatted',
                $sqlWithBindings,
            ));
        });
    }
}
