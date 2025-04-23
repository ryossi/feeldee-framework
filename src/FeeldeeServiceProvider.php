<?php

namespace Feeldee\Framework;

use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Location;
use Feeldee\Framework\Models\Photo;
use Feeldee\Framework\Models\Post;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

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
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'feeldee');

        $this->publishes([
            __DIR__ . '/../lang' => $this->app->langPath('vendor/feeldee'),
        ]);

        $this->publishes([
            __DIR__ . '/../config/feeldee.php' => config_path('feeldee.php'),
        ], 'feeldee');

        AboutCommand::add('Feeldee', fn() => ['Framework Version' => '1.0.0']);

        // カスタムポリモーフィックタイプ
        Relation::enforceMorphMap([
            Post::type() => 'Feeldee\Framework\Models\Post',
            Photo::type() => 'Feeldee\Framework\Models\Photo',
            Location::type() => 'Feeldee\Framework\Models\Location',
            Item::type() => 'Feeldee\Framework\Models\Item',
        ]);
    }
}
