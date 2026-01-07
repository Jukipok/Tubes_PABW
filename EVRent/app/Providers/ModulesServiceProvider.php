<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModulesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $modulesPath = app_path('Modules');

        if (is_dir($modulesPath)) {
            $modules = array_filter(glob($modulesPath . '/*'), 'is_dir');

            foreach ($modules as $module) {
                // Load Routes
                if (file_exists($module . '/Routes/web.php')) {
                    Route::middleware('web')
                        ->group($module . '/Routes/web.php');
                }
            }
        }
    }
}
