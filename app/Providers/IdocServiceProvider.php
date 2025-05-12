<?php

namespace App\Providers;

use App\Services\IdocGeneratorService;
use Illuminate\Support\ServiceProvider;

class IdocServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(IdocGeneratorService::class, function ($app) {
            return new IdocGeneratorService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Se verifică și creează directoarele la pornirea aplicației
        if ($this->app->runningInConsole()) {
            $directories = [
                'IDOC_client',
                'IDOC_furnizor',
                'documente_site/facturi',
                'documente_site/avize',
                'documente_site/comenzi',
            ];
            
            foreach ($directories as $directory) {
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
            }
        }
    }
}
