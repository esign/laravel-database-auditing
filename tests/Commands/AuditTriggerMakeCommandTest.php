<?php

namespace Esign\DatabaseAuditing\Tests\Commands;

use Esign\DatabaseAuditing\Commands\AuditTriggerMakeCommand;
use Esign\DatabaseAuditing\Tests\TestCase;
use Esign\DatabaseTrigger\Commands\MigrationCreator;
use Esign\DatabaseTrigger\Enums\TriggerEvent;
use Esign\DatabaseTrigger\Enums\TriggerTiming;
use Illuminate\Support\Composer;

class AuditTriggerMakeCommandTest extends TestCase
{
    /** @test */
    public function it_can_run_the_audit_trigger_command()
    {
        $this->mock(MigrationCreator::class, function ($mock) {
            $mock->shouldReceive('createTrigger')->once();
        });

        $this->mock(Composer::class, function ($mock) {
            $mock->shouldReceive('dumpAutoloads')->once();
        });

        $this->artisan(AuditTriggerMakeCommand::class)
            ->expectsQuestion('What should the trigger table be?', 'posts')
            ->expectsChoice('What should the trigger event be?', TriggerEvent::UPDATE->value, TriggerEvent::values())
            ->expectsChoice('What should the trigger timing be?', TriggerTiming::AFTER->value, TriggerTiming::values())
            ->expectsQuestion('What\'s the auditable type for the [posts] table? In case you use a morph map you may also provide an alias.', 'post')
            ->expectsQuestion('What\'s the auditable id for the [posts] table?', 'id')
            ->expectsQuestion('What columns should be tracked on the [posts] table? (comma separated)', 'title,slug')
            ->assertSuccessful();
    }
}
