<?php

namespace App\Providers;

use App\Http\Middleware\SetCountryCode;
use App\Models\Product;
use App\Support\LimitedPaginator;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\Builder as ScoutBuilder;
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
        if (app()->environment('local')) {
            Debugbar::enable();
        } else {
            Debugbar::disable();
        }

        $locale = config('app.locale');

        Carbon::setLocale($locale);
        setlocale(LC_TIME, $locale);
        // DB::statement("SET lc_time_names = '{$locale}'");

        Model::unguard();

        Livewire::addPersistentMiddleware([SetCountryCode::class]);

        $this->addLimitedPaginationToQueries();
        $this->addSqlBindingsToSentryLogs();
    }

    private function addLimitedPaginationToQueries()
    {
        Builder::macro('limitedPaginate', function ($perPage = Product::PAGE_SIZE) {
            return LimitedPaginator::fromQuery($this, $perPage, $perPage * Product::MAX_PAGES);
        });

        ScoutBuilder::macro('limitedPaginate', function ($perPage = Product::PAGE_SIZE, $pageName = 'page', $page = null) {
            return $this->paginate($perPage, $pageName, $page);
        });
    }

    private function addSqlBindingsToSentryLogs()
    {
        DB::listen(function ($query) {
            // combine query with its bindings
            $pdo = DB::getPdo();
            $q = str_replace('?', '%s', str_replace('%', '%%', $query->sql));

            $sqlWithBindings = vsprintf($q, array_map(function ($value) use ($pdo) {
                return is_null($value) ? 'NULL' : $pdo->quote($value);
            }, $query->bindings));

            $sqlWithBindings = str_replace('%%', '%', $sqlWithBindings);

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
