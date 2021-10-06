<?php

namespace Dyce\LaravelBouncer;

use Illuminate\Auth\RequestGuard;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class LaravelBouncerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        config([
            'auth.guards.bouncer' => array_merge([
                'driver' => 'bouncer',
                'provider' => null,
            ], config('auth.guards.bouncer', [])),
        ]);

        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__ . '/../config/bouncer.php', 'bouncer');
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerMigrations();

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'bouncer-migrations');

            $this->publishes([
                __DIR__ . '/../config/bouncer.php' => config_path('bouncer.php'),
            ], 'bouncer-config');
        }

        $this->configureGuard();
        $this->configureMiddleware();
    }

    /**
     * Register LaravelBouncer's migration files.
     *
     * @return void
     */
    protected function registerMigrations()
    {
        if (LaravelBouncer::shouldRunMigrations()) {
            return $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    /**
     * Configure the LaravelBouncer authentication guard.
     *
     * @return void
     */
    protected function configureGuard()
    {
        Auth::resolved(function ($auth) {
            $auth->extend('bouncer', function ($app, $name, array $config) use ($auth) {
                return tap($this->createGuard($auth, $config), function ($guard) {
                    app()->refresh('request', $guard, 'setRequest');
                });
            });
        });
    }

    /**
     * Register the guard.
     *
     * @param \Illuminate\Contracts\Auth\Factory  $auth
     * @param string $type
     * @param array $config
     * @return RequestGuard
     */
    protected function createGuard($auth, $type, $config)
    {
        return new RequestGuard(
            new Guard($auth, config('bouncer.expiration.'.$type), $config['provider']),
            $this->app['request'],
            $auth->createUserProvider($config['provider'] ?? null)
        );
    }

    /**
     * Configure the LaravelBouncer middleware and priority.
     *
     * @return void
     */
    protected function configureMiddleware()
    {
        $kernel = $this->app->make(Kernel::class);
    }
}
