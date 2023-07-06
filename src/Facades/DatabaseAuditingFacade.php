<?php

namespace Esign\DatabaseAuditing\Facades;

use Illuminate\Support\Facades\Facade;

class DatabaseAuditingFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'database-auditing';
    }
}
