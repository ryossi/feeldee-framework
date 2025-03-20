<?php

namespace Feeldee\Framework;

use Illuminate\Support\ServiceProvider;

class FeeldeeServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/feeldee.php',
            'feeldee'
        );
    }

    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [];

    /**
     * All of the container singletons that should be registered.
     *
     * @var array
     */
    public $singletons = [];

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 追加設定
        $this->publishes([
            __DIR__ . '/../config/feeldee.php' => config_path('feeldee.php'),
        ], 'feeldee');

        // 追加マイグレーション
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
