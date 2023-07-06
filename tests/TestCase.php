<?php

namespace Esign\DatabaseAuditing\Tests;

use Esign\DatabaseAuditing\DatabaseAuditingServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [DatabaseAuditingServiceProvider::class];
    }
} 