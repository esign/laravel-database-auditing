<?php

namespace Esign\DatabaseAuditing\Tests\Commands;

use Esign\DatabaseAuditing\Commands\MigrationCreator;
use Esign\DatabaseAuditing\Models\Audit;
use Esign\DatabaseAuditing\Tests\Support\Concerns\CleansPublishedMigrations;
use Esign\DatabaseAuditing\Tests\Support\Concerns\MakesAuditTrigger;
use Esign\DatabaseAuditing\Tests\Support\Models\Post;
use Esign\DatabaseAuditing\Tests\TestCase;
use Esign\DatabaseTrigger\Enums\TriggerEvent;
use Esign\DatabaseTrigger\Enums\TriggerTiming;
use Esign\DatabaseTrigger\Facades\Schema;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Support\Composer;

class AuditTriggerMakeCommandTest extends TestCase
{
    use MakesAuditTrigger;
    use CleansPublishedMigrations;

    protected function tearDown(): void
    {
        $this->cleanLaravelMigrationsFolder();

        parent::tearDown();
    }

    /** @test */
    public function it_can_run_the_audit_trigger_command()
    {
        $this->mock(MigrationCreator::class, function ($mock) {
            $mock->shouldReceive('createTrigger')->once();
        });

        $this->mock(Composer::class, function ($mock) {
            $mock->shouldReceive('dumpAutoloads')->once();
        });

        $auditTriggerCommand = $this->makeAuditTrigger(
            triggerTable: 'posts',
            triggerEvent: TriggerEvent::UPDATE,
            triggerTiming: TriggerTiming::AFTER,
            auditableType: 'post',
            auditableId: 'id',
            columnsToBeTracked: ['title', 'slug']
        );

        $auditTriggerCommand->assertSuccessful();
    }

    /** @test */
    public function it_can_create_a_database_trigger_when_the_command_is_executed()
    {
        $auditTriggerCommand = $this->makeAuditTrigger(
            triggerTable: 'posts',
            triggerEvent: TriggerEvent::UPDATE,
            triggerTiming: TriggerTiming::AFTER,
            auditableType: 'post',
            auditableId: 'id',
            columnsToBeTracked: ['title', 'slug']
        );

        $auditTriggerCommand->run();
        $this->artisan(MigrateCommand::class);

        $this->assertTrue(Schema::hasTrigger('audit_after_posts_update'));
    }

    /** @test */
    public function it_can_create_an_audit_using_an_update_event()
    {
        $post = Post::create(['title' => 'My Title', 'slug' => 'my-title']);
        $auditTriggerCommand = $this->makeAuditTrigger(
            triggerTable: 'posts',
            triggerEvent: TriggerEvent::UPDATE,
            triggerTiming: TriggerTiming::AFTER,
            auditableType: 'post',
            auditableId: 'id',
            columnsToBeTracked: ['title', 'slug']
        );
        $auditTriggerCommand->run();
        $this->artisan(MigrateCommand::class);

        $post->update(['title' => 'My Updated Title', 'slug' => 'my-updated-title']);

        $this->assertDatabaseHas(Audit::class, [
            'event' => 'update',
            'auditable_id' => $post->getKey(),
            'auditable_type' => $post->getMorphClass(),
            'old_data' => $this->castAsJson(['title' => 'My Title', 'slug' => 'my-title']),
            'new_data' => $this->castAsJson(['title' => 'My Updated Title', 'slug' => 'my-updated-title']),
        ]);
    }

    /** @test */
    public function it_can_create_an_audit_using_an_insert_event()
    {
        $auditTriggerCommand = $this->makeAuditTrigger(
            triggerTable: 'posts',
            triggerEvent: TriggerEvent::INSERT,
            triggerTiming: TriggerTiming::AFTER,
            auditableType: 'post',
            auditableId: 'id',
            columnsToBeTracked: ['title', 'slug']
        );
        $auditTriggerCommand->run();
        $this->artisan(MigrateCommand::class);

        Post::create(['title' => 'My Title', 'slug' => 'my-title']);

        $this->assertDatabaseHas(Audit::class, [
            'event' => TriggerEvent::INSERT,
            'auditable_type' => (new Post())->getMorphClass(),
            'old_data' => null,
            'new_data' => $this->castAsJson(['title' => 'My Title', 'slug' => 'my-title']),
        ]);
    }

    /** @test */
    public function it_can_create_an_audit_using_a_delete_event()
    {
        $post = Post::create(['title' => 'My Title', 'slug' => 'my-title']);
        $auditTriggerCommand = $this->makeAuditTrigger(
            triggerTable: 'posts',
            triggerEvent: TriggerEvent::DELETE,
            triggerTiming: TriggerTiming::AFTER,
            auditableType: 'post',
            auditableId: 'id',
            columnsToBeTracked: ['title', 'slug']
        );
        $auditTriggerCommand->run();
        $this->artisan(MigrateCommand::class);

        $post->delete();

        $this->assertDatabaseHas(Audit::class, [
            'event' => TriggerEvent::DELETE,
            'auditable_id' => $post->getKey(),
            'auditable_type' => $post->getMorphClass(),
            'old_data' => $this->castAsJson(['title' => 'My Title', 'slug' => 'my-title']),
            'new_data' => null,
        ]);
    }
}
