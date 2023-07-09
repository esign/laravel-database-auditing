<?php

namespace Esign\DatabaseAuditing;

use Esign\DatabaseTrigger\Enums\TriggerEvent;

class AuditTriggerStatement
{
    public function __construct(
        public TriggerEvent $triggerEvent,
        public string $auditableType,
        public string $auditableId,
        public array $columnsToBeTracked,
    ) {
    }
}
