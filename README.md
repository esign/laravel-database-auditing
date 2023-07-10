# Track database changes in Laravel using database triggers.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/esign/laravel-database-auditing.svg?style=flat-square)](https://packagist.org/packages/esign/laravel-database-auditing)
[![Total Downloads](https://img.shields.io/packagist/dt/esign/laravel-database-auditing.svg?style=flat-square)](https://packagist.org/packages/esign/laravel-database-auditing)
![GitHub Actions](https://github.com/esign/laravel-database-auditing/actions/workflows/main.yml/badge.svg)

This package allows you to track changes in your database using database triggers. Currently only MySQL is supported.

>**Note**
> This package is designed to track database changes that occur outside of Laravel.
> However, for changes originating within a Laravel application, other solutions like [owen-it/laravel-auditing](https://github.com/owen-it/laravel-auditing) may better suit your requirements.

## Installation

You can install the package via composer:

```bash
composer require esign/laravel-database-auditing
```

The package will automatically register a service provider.

This package comes with a migration to store your database changes. You can publish the migration file:
```bash
php artisan vendor:publish --provider="Esign\DatabaseAuditing\DatabaseAuditingServiceProvider" --tag="migrations"
```

Next up, you can optionally publish the configuration file:
```bash
php artisan vendor:publish --provider="Esign\DatabaseAuditing\DatabaseAuditingServiceProvider" --tag="config"
```

The config file will be published as config/database-auditing.php with the following contents:
```php
return [
    /**
     * Specifies the model used by the package to retrieve audits.
     */
    'model' => Esign\DatabaseAuditing\Models\Audit::class,
];
```

## Usage
### Creating database triggers
To track database changes, use the `php artisan make:audit-trigger` command. This will generate a migration file with the necessary trigger configuration:
```php
use Esign\DatabaseTrigger\DatabaseTrigger;
use Esign\DatabaseTrigger\Enums\TriggerEvent;
use Esign\DatabaseTrigger\Enums\TriggerTiming;
use Esign\DatabaseTrigger\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::createTrigger('audit_after_posts_insert', function (DatabaseTrigger $trigger) {
            $trigger->on('posts');
            $trigger->timing(TriggerTiming::AFTER);
            $trigger->event(TriggerEvent::INSERT);
            $trigger->statement("
                insert into audits (
                    event,
                    auditable_type,
                    auditable_id,
                    old_data,
                    new_data
                ) values (
                    'insert',
                    'post',
                    NEW.id,
                    NULL,
                    JSON_OBJECT('title', NEW.title, 'slug', NEW.slug)
                );
            ");
        });
    }

    public function down(): void
    {
        Schema::dropTriggerIfExists('audit_after_posts_insert');
    }
};
```

By default, a trigger name will be automatically assigned based on the provided input. However, you can specify a different name by passing it as the first argument:
```bash
php artisan make:audit-trigger my_trigger
```

### Retrieving tracked changes

After running the trigger migration, any modifications made to the associated table will be automatically monitored and stored in the `audits` table.

To retrieve the recorded changes in your Laravel project, you can utilize the `Esign\DatabaseAuditing\Models\Audit` model provided by the package.
Here's an example:
```php
use Esign\DatabaseAuditing\Models\Audit;

$audits = Audit::query()->get();
```

To determine if any data changes occurred in an audit, you can utilize the `hasDataChanges()` method available on the `Audit` model.
Here's how you can use it:
```php
use Esign\DatabaseAuditing\Models\Audit;

$latestAudit = Audit::latest()->first();
$latestAudit->hasDataChanges();
$latestAudit->hasDataChanges('slug');
```

The `hasDataChanges()` method returns a boolean value indicating whether any changes were made.
If you pass a specific attribute name as an argument, it will check for changes in that particular attribute only.

To retrieve audits based on specific trigger events, you can use the `event()` scope provided by the `Audit` model.
Here's an example:
```php
use Esign\DatabaseAuditing\Models\Audit;
use Esign\DatabaseTrigger\Enums\TriggerEvent;

Audit::event(TriggerEvent::UPDATE)->first();
```

### Tracking changes in Eloquent models

To track changes related to your Eloquent model, apply the `Esign\DatabaseAuditing\Concerns\HasAudits` trait to the respective model. For instance:
```php
use Esign\DatabaseAuditing\Concerns\HasAudits;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasAudits;
}
```

This will enable an `audits` relationship on the model, allowing you to access the tracked changes. For example:
```php
$post = Post::first();
$latestAudit = $post->audits()->latest()->first();
```

Feel free to explore the [`Esign\DatabaseAuditing\Models\Audit`](./src/Models/Audit.php) model for more functionality related to tracking and retrieving changes.

### Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
