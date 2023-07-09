<?php

namespace Esign\DatabaseAuditing\Tests\Support\Concerns;

use Illuminate\Filesystem\Filesystem;

trait CleansPublishedMigrations
{
    public function cleanLaravelMigrationsFolder()
    {
        (new Filesystem())->cleanDirectory(database_path('migrations'));
    }
}
