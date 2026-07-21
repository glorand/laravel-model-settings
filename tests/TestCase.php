<?php

namespace Glorand\Model\Settings\Tests;

use CreateModelSettingsTable;
use Glorand\Model\Settings\ModelSettingsServiceProvider;
use Glorand\Model\Settings\Tests\Models\UsersWithTable;
use Glorand\Model\Settings\Tests\Models\UserWithField;
use Glorand\Model\Settings\Tests\Models\UserWithRedis;
use Glorand\Model\Settings\Tests\Models\UserWithTextField;
use Glorand\Model\Settings\Tests\Models\WrongUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunaweb\RedisMock\Providers\RedisMockServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
        $this->checkRequirements();
    }

    protected function checkRequirements()
    {
        //
    }

    /**
     * @return array<int, string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            ModelSettingsServiceProvider::class,
        ];
    }

    /**
     * @param mixed $app
     */
    public function getEnvironmentSetUp($app): void
    {
        $app['config']->set('auth.providers.users.model', UserWithField::class);
        $app['config']->set('database.redis.client', 'mock');
        $app['config']->set('database.default', 'testing');
        $app->register(RedisMockServiceProvider::class);
    }

    protected function setUpDatabase(): void
    {
        $this->createSettingsTable();

        $this->createTables('users', 'users_with_table', 'users_with_field', 'users_with_text_field', 'wrong_users');
        $this->seedModels(UserWithField::class, UserWithTextField::class, UsersWithTable::class, WrongUser::class, UserWithRedis::class);
    }

    protected function createSettingsTable(): void
    {
        include_once __DIR__ . '/migrations/create_model_settings_table.php';

        (new CreateModelSettingsTable())->up();
    }

    protected function createTables(...$tableNames): void
    {
        collect($tableNames)->each(function (string $tableName) {
            Schema::create($tableName, function (Blueprint $table) use ($tableName) {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->timestamps();

                if ('users_with_field' === $tableName) {
                    $table->json('settings')->nullable();
                }

                if ('users_with_text_field' === $tableName) {
                    $table->text('settings')->nullable();
                }
            });
        });
    }

    protected function seedModels(...$modelClasses): void
    {
        collect($modelClasses)->each(function (string $modelClass) {
            foreach (range(1, 2) as $index) {
                $modelClass::create(['name' => "name $index"]);
            }
        });
    }

    public function markTestAsPassed(): void
    {
        $this->assertTrue(true);
    }

    protected function getModelByType(string $type): Model
    {
        return match ($type) {
            'table' => UsersWithTable::first(),
            'text_field' => UserWithTextField::first(),
            'redis' => UserWithRedis::first(),
            default => UserWithField::first(),
        };
    }
}
