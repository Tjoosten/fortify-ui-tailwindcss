<?php

namespace Pradeep\FortifyUITailwind\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class FortifyUITailwindCommand extends Command
{
    public $signature = 'fortify-ui:tailwindcss {--skip-provider}';

    public $description = 'Setup FortifyUI routes, service providers and views with Tailwindcss preset';

    public function handle()
    {
        $this->publishAssets();
        $this->updateServiceProviders();
        $this->updateRoutes();

        $this->comment('FortifyUI Tailwind CSS is now installed.');

        if ($this->option('skip-provider')) {
            $this->info('Please, remember to include the Fortify view registrations!');
        }

        $this->info('Please, run php artisan migrate!');
    }

    protected function publishAssets()
    {
        $this->callSilent('vendor:publish', ['--provider' => 'Laravel\Fortify\FortifyServiceProvider']);

        if (! $this->option('skip-provider')) {
            $this->callSilent('vendor:publish', ['--tag' => 'fortifyui-provider', '--force' => true]);
        }

        $this->callSilent('vendor:publish', ['--tag' => 'fortifyui-views', '--force' => true]);
    }

    public function updateServiceProviders()
    {
        $appConfig = file_get_contents(config_path('app.php'));

        if ($this->option('skip-provider')) {
            if (! Str::contains($appConfig, 'App\\Providers\\FortifyServiceProvider::class')) {
                file_put_contents(config_path('app.php'), str_replace(
                    "App\Providers\RouteServiceProvider::class,",
                    "App\Providers\RouteServiceProvider::class,".PHP_EOL."        App\Providers\FortifyServiceProvider::class,",
                    $appConfig
                ));
            }
        } else {
            if (
                ! Str::contains($appConfig, 'App\\Providers\\FortifyServiceProvider::class')
                &&
                ! Str::contains($appConfig, 'App\\Providers\\FortifyUITailwindServiceProvider::class')
            ) {
                file_put_contents(config_path('app.php'), str_replace(
                    "App\Providers\RouteServiceProvider::class,",
                    "App\Providers\RouteServiceProvider::class,".PHP_EOL."        App\Providers\FortifyServiceProvider::class,".PHP_EOL."        App\\Providers\\FortifyUITailwindServiceProvider::class",
                    $appConfig
                ));
            }
        }
    }

    protected function updateRoutes()
    {
        file_put_contents(
            base_path('routes/web.php'),
            "\nRoute::view('home', 'home')\n\t->name('home')\n\t->middleware(['auth', 'verified']);\n",
            FILE_APPEND
        );
    }
}