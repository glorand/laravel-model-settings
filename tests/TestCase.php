<?php

namespace Glorand\Model\Settings\Tests;

use CreateModelSettingsTable;
use Glorand\Model\Settings\ModelSettingsServiceProvider;
use Glorand\Model\Settings\Tests\Models\Article;
use Glorand\Model\Settings\Tests\Models\User;
use Glorand\Model\Settings\Tests\Models\UsersWithTable;
use Glorand\Model\Settings\Tests\Models\UserWithField;
use Glorand\Model\Settings\Tests\Models\WrongUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
        $this->checkRequirements();
    }

    protected function checkRequirements()
    {
        collect($this->getAnnotations())->filter(function ($location) {
            return in_array('!Travis', Arr::get($location, 'requires', []));
        })->each(function ($location) {
            $this->markTestSkipped('Travis will not run this test.');
        });
    }

    protected function getPackageProviders($app)
    {
        return [
            ModelSettingsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('auth.providers.users.model', UserWithField::class);
    }

    protected function setUpDatabase()
    {
        $this->createSettingsTable();

        $this->createTables('users_with_table', 'users_with_field', 'wrong_users');
        $this->seedModels(UserWithField::class, UsersWithTable::class, WrongUser::class);
    }

    protected function createSettingsTable()
    {
        include_once __DIR__ . '/migrations/create_model_settings_table.php';

        (new CreateModelSettingsTable())->up();
    }

    protected function createTables(...$tableNames)
    {
        collect($tableNames)->each(function (string $tableName) {
            Schema::create($tableName, function (Blueprint $table) use ($tableName) {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->timestamps();

                if ('users_with_field' === $tableName) {
                    $table->json('settings')->nullable();
                }
            });
        });
    }

    protected function seedModels(...$modelClasses)
    {
        collect($modelClasses)->each(function (string $modelClass) {
            foreach (range(1, 2) as $index) {
                $modelClass::create(['name' => "name {$index}"]);
            }
        });
    }

    public function markTestAsPassed()
    {
        $this->assertTrue(true);
    }
}
