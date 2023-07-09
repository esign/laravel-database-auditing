<?php

namespace Esign\DatabaseAuditing\Tests\Support\Concerns;

use Esign\DatabaseAuditing\Commands\AuditTriggerMakeCommand;
use Esign\DatabaseTrigger\Enums\TriggerEvent;
use Esign\DatabaseTrigger\Enums\TriggerTiming;
use Illuminate\Testing\PendingCommand;

trait MakesAuditTrigger
{
    protected function makeAuditTrigger(
        string $triggerTable,
        TriggerEvent $triggerEvent,
        TriggerTiming $triggerTiming,
        string $auditableType,
        string $auditableId,
        array $columnsToBeTracked,
    ): PendingCommand {
        return $this->artisan(AuditTriggerMakeCommand::class)
            ->expectsQuestion('What should the trigger table be?', $triggerTable)
            ->expectsChoice('What should the trigger event be?', $triggerEvent->value, TriggerEvent::values())
            ->expectsChoice('What should the trigger timing be?', $triggerTiming->value, TriggerTiming::values())
            ->expectsQuestion("What's the auditable type for the [{$triggerTable}] table? In case you use a morph map you may also provide an alias.", $auditableType)
            ->expectsQuestion("What's the auditable id for the [{$triggerTable}] table?", $auditableId)
            ->expectsQuestion("What columns should be tracked on the [{$triggerTable}] table? (comma separated)", implode(',', $columnsToBeTracked));
    }
}
