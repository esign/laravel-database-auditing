<?php

namespace Esign\DatabaseAuditing\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasAudits
{
    public function audits(): MorphMany
    {
        return $this->morphMany(
            config('database-auditing.model'),
            'auditable'
        );
    }
}
