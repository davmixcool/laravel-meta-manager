<?php

namespace Davmixcool\MetaManager;

use Davmixcool\MetaManager\Console\CheckMetaCommand;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

/**
 * NOTE: this class name and namespace are part of the public API.
 *
 * v1's README instructs users to register `Davmixcool\MetaManager\MetaServiceProvider`
 * by hand in config/app.php, and `vendor:publish --provider="..."` names it too.
 * Moving or renaming it would break those applications, so it stays exactly here.
 */
class MetaServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/views', 'meta');

        $this->publishes([
            __DIR__.'/config/meta.php' => config_path('meta.php'),
        ], 'meta-config');

        Blade::directive('meta', static fn () => "<?php echo app('meta')->render(); ?>");

        if ($this->app->runningInConsole()) {
            $this->commands([
                CheckMetaCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        // v1 shipped no mergeConfigFrom, so applications that never published
        // config/meta.php resolved every key as null. Merging here gives them
        // the documented defaults - see UPGRADE.md, this is an intentional
        // behaviour change for that cohort.
        $this->mergeConfigFrom(__DIR__.'/config/meta.php', 'meta');

        // scoped() is a singleton that Octane flushes between requests, which
        // is what keeps one page's metadata from leaking onto the next.
        $this->app->scoped('meta', static fn () => new MetaManager());

        $this->app->alias('meta', MetaManager::class);
    }

    public function provides(): array
    {
        return ['meta', MetaManager::class];
    }
}
