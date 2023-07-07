<?php

namespace Esign\DatabaseAuditing\Models;

use Esign\DatabaseTrigger\Enums\TriggerEvent;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Audit extends Model
{
    protected $guarded = [];
    protected $casts = [
        'old_data' => 'json',
        'new_data' => 'json',
    ];

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function hasDataChanges(string $key = null): bool
    {
        return data_get($this->old_data, $key) !== data_get($this->new_data, $key);
    }

    public function scopeEvent(Builder $query, TriggerEvent $triggerEvent): Builder
    {
        return $query->where('event', $triggerEvent->value);
    }
}
