<?php

namespace Techsemicolon\Sanitizer;

use Illuminate\Support\ServiceProvider;
use Techsemicolon\Sanitizer\Console\SanitizeDatabaseCommand;

class DatabaseSanitizerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/sanitizer.php' => config_path('sanitizer.php'),
        ]);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SanitizeDatabaseCommand::class
            ]);
        }
    }
}
