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

        $this->publishes([
            __DIR__ . '/../config/feeldee.php' => config_path('feeldee.php'),
        ], 'feeldee');

        AboutCommand::add('Feeldee', fn() => ['Framework Version' => '1.0.0']);

        // カスタムポリモーフィックタイプ
        Relation::enforceMorphMap([
            Post::TYPE => 'Feeldee\Framework\Models\Post',
            Photo::TYPE => 'Feeldee\Framework\Models\Photo',
            Location::TYPE => 'Feeldee\Framework\Models\Location',
            Item::TYPE => 'Feeldee\Framework\Models\Item',
        ]);
    }
}
