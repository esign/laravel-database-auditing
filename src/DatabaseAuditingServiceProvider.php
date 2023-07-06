<?php

namespace Esign\DatabaseAuditing;

use Illuminate\Support\ServiceProvider;

class DatabaseAuditingServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([$this->configPath() => config_path('database-auditing.php')], 'config');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'database-auditing');

        $this->app->singleton('database-auditing', function () {
            return new DatabaseAuditing;
        });
    }

    protected function configPath(): string
    {
        return __DIR__ . '/../config/database-auditing.php';
    }
}
