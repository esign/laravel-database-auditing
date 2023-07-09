<?php

namespace Esign\DatabaseAuditing\Commands;

use Esign\DatabaseAuditing\AuditTriggerStatement;
use Esign\DatabaseAuditing\Database\MySqlGrammar;
use Esign\DatabaseTrigger\Commands\TriggerMakeCommand;
use Esign\DatabaseTrigger\DatabaseTrigger;
use Esign\DatabaseTrigger\Enums\TriggerEvent;
use Esign\DatabaseTrigger\Enums\TriggerTiming;
use Illuminate\Support\Composer;

class AuditTriggerMakeCommand extends TriggerMakeCommand
{
    protected $signature = 'make:audit-trigger {name? : The name of the trigger}
        {--path= : The location where the migration file should be created}
        {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
        {--fullpath : Output the full path of the migration}';
    protected $description = 'Create a new audit trigger migration';

    public function __construct(
        MigrationCreator $creator,
        Composer $composer
    ) {
        parent::__construct($creator, $composer);
    }

    public function handle(): void
    {
        $triggerTable = $this->ask('What should the trigger table be?');
        $triggerEvent = $this->choice('What should the trigger event be?', TriggerEvent::values());
        $triggerTiming = $this->choice('What should the trigger timing be?', TriggerTiming::values());
        $auditableType = $this->ask("What's the auditable type for the [$triggerTable] table? In case you use a morph map you may also provide an alias.");
        $auditableId = $this->ask("What's the auditable id for the [$triggerTable] table?");
        $columnsToBeTracked = explode(',', $this->ask("What columns should be tracked on the [$triggerTable] table? (comma separated)"));
        $triggerName = $this->argument('name') ?? implode('_', ['audit', $triggerTiming, $triggerTable, $triggerEvent]);
        $trigger = (new DatabaseTrigger())
            ->name($triggerName)
            ->on($triggerTable)
            ->event(TriggerEvent::from($triggerEvent))
            ->timing(TriggerTiming::from($triggerTiming))
            ->statement(function () use ($triggerEvent, $auditableType, $auditableId, $columnsToBeTracked) {
                $auditTriggerStatement = new AuditTriggerStatement(
                    TriggerEvent::from($triggerEvent),
                    $auditableType,
                    $auditableId,
                    $columnsToBeTracked,
                );

                return (new MySqlGrammar())->compileAuditTriggerStatement($auditTriggerStatement);
            });

        $this->writeMigration($trigger);

        $this->composer->dumpAutoloads();
    }
}
