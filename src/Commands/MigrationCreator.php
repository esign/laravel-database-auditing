<?php

namespace Esign\DatabaseAuditing\Commands;

use Esign\DatabaseTrigger\Commands\MigrationCreator as BaseMigrationCreator;

class MigrationCreator extends BaseMigrationCreator
{
    public function stubPath(): string
    {
        return __DIR__ . '/stubs';
    }
}
