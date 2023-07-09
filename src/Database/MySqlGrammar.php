<?php

namespace Esign\DatabaseAuditing\Database;

use Esign\DatabaseAuditing\AuditTriggerStatement;
use Esign\DatabaseTrigger\Enums\TriggerEvent;
use Illuminate\Database\Query\Grammars\MySqlGrammar as BaseMySqlGrammar;

class MySqlGrammar extends BaseMySqlGrammar
{
    public function compileAuditTriggerStatement(AuditTriggerStatement $statement): string
    {
        return sprintf(
            'insert into audits (event, auditable_type, auditable_id, old_data, new_data) values (%s, %s, %s, %s, %s);',
            $this->quoteString($statement->triggerEvent->value),
            $this->quoteString($statement->auditableType),
            $this->compileAuditableIdByTriggerEvent($statement->triggerEvent, $statement->auditableId),
            $this->compileOldColumnsToBeTrackedByTriggerEvent($statement->triggerEvent, $statement->columnsToBeTracked),
            $this->compileNewColumnsToBeTrackedByTriggerEvent($statement->triggerEvent, $statement->columnsToBeTracked),
        );
    }

    protected function compileAuditableIdByTriggerEvent(TriggerEvent $triggerEvent, string $auditableId): string
    {
        return match ($triggerEvent) {
            TriggerEvent::UPDATE => "OLD.{$auditableId}",
            TriggerEvent::INSERT => "NEW.{$auditableId}",
            TriggerEvent::DELETE => "OLD.{$auditableId}",
        };
    }

    protected function compileOldColumnsToBeTrackedByTriggerEvent(TriggerEvent $triggerEvent, array $columnsToBeTracked): string
    {
        return match ($triggerEvent) {
            TriggerEvent::INSERT => 'NULL',
            TriggerEvent::UPDATE => $this->compileColumnsToBeTracked($columnsToBeTracked, 'OLD'),
            TriggerEvent::DELETE => $this->compileColumnsToBeTracked($columnsToBeTracked, 'OLD'),
        };
    }

    protected function compileNewColumnsToBeTrackedByTriggerEvent(TriggerEvent $triggerEvent, array $columnsToBeTracked): string
    {
        return match ($triggerEvent) {
            TriggerEvent::INSERT => $this->compileColumnsToBeTracked($columnsToBeTracked, 'NEW'),
            TriggerEvent::UPDATE => $this->compileColumnsToBeTracked($columnsToBeTracked, 'NEW'),
            TriggerEvent::DELETE => 'NULL',
        };
    }

    protected function compileColumnsToBeTracked(array $columnsToBeTracked, string $rowKeyword): string
    {
        $formattedColumnsToBeTracked = array_map(function (string $columnToBeTracked) use ($rowKeyword) {
            return sprintf(
                '%s, %s.%s',
                $this->quoteString($columnToBeTracked),
                $rowKeyword,
                $columnToBeTracked
            );
        }, $columnsToBeTracked);

        return sprintf(
            'JSON_OBJECT(%s)',
            implode(', ', $formattedColumnsToBeTracked)
        );
    }
}
