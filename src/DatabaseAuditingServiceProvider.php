<?php

namespace Esign\DatabaseAuditing;

use Esign\DatabaseAuditing\Commands\AuditTriggerMakeCommand;
use Illuminate\Support\ServiceProvider;

class DatabaseAuditingServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([AuditTriggerMakeCommand::class]);

            $this->publishes([
                $this->configPath() => config_path('database-auditing.php'),
            ], 'config');

            $this->publishes([
                $this->migrationPath() => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_audits_table.php'),
            ], 'migrations');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'database-auditing');
    }

    protected function configPath(): string
    {
        return __DIR__ . '/../config/database-auditing.php';
    }

    protected function migrationPath(): string
    {
        return __DIR__ . '/../database/migrations/create_audits_table.php.stub';
    }
}
