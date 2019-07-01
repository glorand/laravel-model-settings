<?php

namespace Glorand\Model\Settings\Tests;

class ConsoleCommandTest extends TestCase
{
    public function testModelSettingsTableCommand()
    {
        //default
        $this->artisan('model-settings:model-settings-table')
            ->assertExitCode(0);

        //custom
        config(['model_settings.settings_table_name' => 'custom_settings_table']);
        $this->artisan('model-settings:model-settings-table')
            ->assertExitCode(0);

        //empty table
        config(['model_settings.settings_table_name' => null]);
        $this->artisan('model-settings:model-settings-table')
            ->assertExitCode(0);
    }

    public function testModelSettingsFieldCommand()
    {
        $this->artisan('model-settings:model-settings-field')
            ->expectsQuestion('What is the name of the table?', '')
            ->assertExitCode(0);

        $this->artisan('model-settings:model-settings-field')
            ->expectsQuestion('What is the name of the table?', 'users_with_field')
            ->expectsQuestion('What is the name of the settings field name?', 'settings')
            ->assertExitCode(0);

        $table = 'users_with_table';
        $fieldName = 'custom_settings_field';
        $this->artisan('model-settings:model-settings-field')
            ->expectsQuestion('What is the name of the table?', $table)
            ->expectsQuestion('What is the name of the settings field name?', $fieldName)
            ->assertExitCode(0);

        $this->artisan('model-settings:model-settings-field')
            ->expectsQuestion('What is the name of the table?', $table . '_wrong')
            ->assertExitCode(0);
    }
}
