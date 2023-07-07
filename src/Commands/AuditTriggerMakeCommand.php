<?php

namespace Esign\DatabaseAuditing\Commands;

use Esign\DatabaseTrigger\Commands\TriggerMakeCommand;
use Esign\DatabaseTrigger\DatabaseTrigger;
use Esign\DatabaseTrigger\Enums\TriggerEvent;
use Esign\DatabaseTrigger\Enums\TriggerTiming;

class AuditTriggerMakeCommand extends TriggerMakeCommand
{
    protected $signature = 'make:audit-trigger {name? : The name of the trigger}
        {--path= : The location where the migration file should be created}
        {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
        {--fullpath : Output the full path of the migration}';

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
            ->statement($this->compileStatement(
                TriggerEvent::from($triggerEvent),
                $auditableType,
                $auditableId,
                $columnsToBeTracked,
            ));

        $this->writeMigration($trigger);

        $this->composer->dumpAutoloads();
    }

    protected function compileStatement(
        TriggerEvent $triggerEvent,
        string $auditableType,
        string $auditableId,
        array $columnsToBeTracked,
    ): string {
        [
            'old' => $oldColumnsToBeTracked,
            'new' => $newColumnsToBeTracked,
        ] = $this->compileColmumnsToBeTrackedByTriggerEvent($triggerEvent, $columnsToBeTracked);
        $auditableId = $this->compileAuditableIdByTriggerEvent($triggerEvent, $auditableId);

        return "
                INSERT INTO audits (
                    event,
                    auditable_type,
                    auditable_id,
                    old_data,
                    new_data
                )
                VALUES (
                    \"{$triggerEvent->value}\",
                    \"{$auditableType}\",
                    {$auditableId},
                    {$oldColumnsToBeTracked},
                    {$newColumnsToBeTracked}
                );
        ";
    }

    protected function compileAuditableIdByTriggerEvent(TriggerEvent $triggerEvent, string $auditableId): string
    {
        return match ($triggerEvent) {
            TriggerEvent::UPDATE => "OLD.{$auditableId}",
            TriggerEvent::INSERT => "NEW.{$auditableId}",
            TriggerEvent::DELETE => "OLD.{$auditableId}",
        };
    }

    protected function compileColmumnsToBeTrackedByTriggerEvent(TriggerEvent $triggerEvent, array $columnsToBeTracked): array
    {
        return match ($triggerEvent) {
            TriggerEvent::UPDATE => [
                'old' => $this->compileColumnsToBeTracked($columnsToBeTracked, 'OLD'),
                'new' => $this->compileColumnsToBeTracked($columnsToBeTracked, 'NEW'),
            ],
            TriggerEvent::INSERT => [
                'old' => 'NULL',
                'new' => $this->compileColumnsToBeTracked($columnsToBeTracked, 'NEW'),
            ],
            TriggerEvent::DELETE => [
                'old' => $this->compileColumnsToBeTracked($columnsToBeTracked, 'OLD'),
                'new' => 'NULL',
            ],
        };
    }

    protected function compileColumnsToBeTracked(array $columnsToBeTracked, string $rowKeyword): string
    {
        $formattedColumnsToBeTracked = array_map(function (string $columnToBeTracked) use ($rowKeyword) {
            return "\"{$columnToBeTracked}\", {$rowKeyword}.{$columnToBeTracked}";
        }, $columnsToBeTracked);

        return 'JSON_OBJECT(' . implode(', ', $formattedColumnsToBeTracked) . ')';
    }
}
