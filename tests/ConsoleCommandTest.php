<?php

namespace Glorand\Model\Settings\Tests;

use Illuminate\Support\Facades\Schema;

class ConsoleCommandTest extends TestCase
{
    public function testModelSettingsTableCommand()
    {
        Schema::dropIfExists(config('model_settings.settings_table_name'));
        $this->artisan('model-settings:model-settings-table')
            ->assertExitCode(0);
    }

    public function testAlreadyExistsTable()
    {
        $this->artisan('model-settings:model-settings-table')
            ->assertExitCode(2);
    }

    public function testTableCommandWithNullTableNameConfig()
    {
        config(['model_settings.settings_table_name' => null]);
        $this->assertEquals(null, config('model_settings.settings_table_name'));
        $this->artisan('model-settings:model-settings-table')
            ->assertExitCode(1);
    }

    public function testTableCommandUpdateConfig()
    {
        $this->assertEquals('model_settings', config('model_settings.settings_table_name'));
        $newTableName = 'custom_table_settings';
        config()->set('model_settings.settings_table_name', $newTableName);
        $this->assertEquals($newTableName, config('model_settings.settings_table_name'));
        Schema::dropIfExists($newTableName);
        $this->artisan('model-settings:model-settings-table')
            ->assertExitCode(0);
    }

    public function testModelSettingsFieldCommand()
    {
        $table = 'users_with_table';
        $fieldName = 'custom_settings_field';

        $this->artisan('model-settings:model-settings-field')
            ->expectsQuestion('What is the name of the table?', '')
            ->assertExitCode(1);

        $this->artisan('model-settings:model-settings-field')
            ->expectsQuestion('What is the name of the table?', $table . '_wrong')
            ->assertExitCode(2);

        $this->artisan('model-settings:model-settings-field')
            ->expectsQuestion('What is the name of the table?', 'users_with_field')
            ->expectsQuestion('What is the name of the settings field name?', 'settings')
            ->assertExitCode(3);


        $this->artisan('model-settings:model-settings-field')
            ->expectsQuestion('What is the name of the table?', $table)
            ->expectsQuestion('What is the name of the settings field name?', $fieldName)
            ->assertExitCode(0);
    }
}
