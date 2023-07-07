<?php

namespace Esign\DatabaseAuditing\Tests;

use Esign\DatabaseAuditing\DatabaseAuditingServiceProvider;
use Esign\DatabaseAuditing\Tests\Support\Models\Post;
use Esign\DatabaseTrigger\DatabaseTriggerServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Relation::morphMap([
            'post' => Post::class,
        ]);

        Schema::dropAllTables();

        $migration = include __DIR__ . '/../database/migrations/create_audits_table.php.stub';
        $migration->up();

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
        });
    }

    protected function getPackageProviders($app): array
    {
        return [
            DatabaseAuditingServiceProvider::class,
            DatabaseTriggerServiceProvider::class,
        ];
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('posts');

        parent::tearDown();
    }
}
