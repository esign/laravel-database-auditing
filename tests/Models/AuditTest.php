<?php

namespace Esign\DatabaseAuditing\Tests\Models;

use Esign\DatabaseAuditing\Models\Audit;
use Esign\DatabaseAuditing\Tests\Support\Models\Post;
use Esign\DatabaseAuditing\Tests\TestCase;
use Esign\DatabaseTrigger\Enums\TriggerEvent;

class AuditTest extends TestCase
{
    /** @test */
    public function it_can_report_data_as_changed_correctly()
    {
        $audit = new Audit([
            'old_data' => ['slug' => 'abc'],
            'new_data' => ['slug' => 'abc-1'],
        ]);

        $this->assertTrue($audit->hasDataChanges('slug'));
    }

    /** @test */
    public function it_wont_report_data_as_changed_incorrectly()
    {
        $audit = new Audit([
            'old_data' => ['slug' => 'abc'],
            'new_data' => ['slug' => 'abc'],
        ]);

        $this->assertFalse($audit->hasDataChanges('slug'));
    }

    /** @test */
    public function it_can_scope_by_event()
    {
        $post = Post::create(['title' => 'abc', 'slug' => 'abc']);
        $updatedAudit = Audit::create([
            'auditable_type' => $post->getMorphClass(),
            'auditable_id' => $post->getKey(),
            'event' => TriggerEvent::UPDATE->value,
        ]);
        $insertedAudit = Audit::create([
            'auditable_type' => $post->getMorphClass(),
            'auditable_id' => $post->getKey(),
            'event' => TriggerEvent::INSERT->value,
        ]);

        $audits = Audit::query()->event(TriggerEvent::UPDATE)->get();

        $this->assertTrue($audits->contains($updatedAudit));
        $this->assertFalse($audits->contains($insertedAudit));
    }
}
