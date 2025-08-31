<?php

namespace Feeldee\Framework;

use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Location;
use Feeldee\Framework\Models\Photo;
use Feeldee\Framework\Models\Journal;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\AliasLoader;

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

        // エイリアスを登録 
        AliasLoader::getInstance()->alias(
            'PublicLevel',
            \Feeldee\Framework\Models\PublicLevel::class
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
        ], 'feeldee-lang');

        $this->publishes([
            __DIR__ . '/../config/feeldee.php' => config_path('feeldee.php'),
        ], 'feeldee-config');

        AboutCommand::add('Feeldee', fn() => ['Framework Version' => '1.0.0']);

        // カスタムポリモーフィックタイプ
        Relation::enforceMorphMap([
            Journal::type() => 'Feeldee\Framework\Models\Journal',
            Photo::type() => 'Feeldee\Framework\Models\Photo',
            Location::type() => 'Feeldee\Framework\Models\Location',
            Item::type() => 'Feeldee\Framework\Models\Item',
        ]);
    }
}
