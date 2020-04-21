<?php

namespace Glorand\Model\Settings\Tests;

class CreateSettingsFieldForModelConsoleTest extends TestCase
{
    private $table = 'users_with_field';
    private $fieldName = 'custom_settings_field';
    private $alreadyExistsFieldName = 'settings';

    public function testEmptyTable()
    {
        $this->artisan('model-settings:model-settings-field')
            ->expectsQuestion('What is the name of the table?', '')
            ->assertExitCode(1);
    }

    public function testMissingTable()
    {
        $this->artisan('model-settings:model-settings-field')
            ->expectsQuestion('What is the name of the table?', $this->table . '_wrong')
            ->assertExitCode(2);
    }

    public function testAlreadyExistsField()
    {
        $this->artisan('model-settings:model-settings-field')
            ->expectsQuestion('What is the name of the table?', $this->table)
            ->expectsQuestion('What is the name of the settings field name?', $this->alreadyExistsFieldName)
            ->assertExitCode(3);
    }

    public function testCreateMigrationFile()
    {
        $this->artisan('model-settings:model-settings-field')
            ->expectsQuestion('What is the name of the table?', $this->table)
            ->expectsQuestion('What is the name of the settings field name?', $this->fieldName)
            ->assertExitCode(0);
    }
}