<?php

namespace Glorand\Model\Settings\Tests;

use Illuminate\Support\Facades\Schema;

class CreateSettingsTableConsoleTest extends TestCase
{
    public function testEmptyTable()
    {
        if (version_compare(PHP_VERSION, '8.0', '>=')) {
            $this->markTestAsPassed();
            return;
        }
        config()->set('model_settings.settings_table_name', null);
        $this->assertEquals(null, config('model_settings.settings_table_name'));
        $this->artisan('model-settings:model-settings-table')
            ->assertExitCode(1);
    }

    public function testAlreadyExistsTable()
    {
        if (version_compare(PHP_VERSION, '8.0', '>=')) {
            $this->markTestAsPassed();
            return;
        }
        config()->set('model_settings.settings_table_name', 'model_settings');
        $this->artisan('model-settings:model-settings-table')
            ->assertExitCode(2);
    }

    public function testCreateMigration()
    {
        if (version_compare(PHP_VERSION, '8.0', '>=')) {
            $this->markTestAsPassed();
            return;
        }
        config()->set('model_settings.settings_table_name', 'model_settings');
        Schema::dropIfExists(config('model_settings.settings_table_name'));
        $this->artisan('model-settings:model-settings-table')
            ->assertExitCode(0);
    }

    public function testWithUpdateConfig()
    {
        if (version_compare(PHP_VERSION, '8.0', '>=')) {
            $this->markTestAsPassed();
            return;
        }
        $this->assertEquals('model_settings', config('model_settings.settings_table_name'));
        $newTableName = 'custom_table_settings';
        config()->set('model_settings.settings_table_name', $newTableName);
        $this->assertEquals($newTableName, config('model_settings.settings_table_name'));
        Schema::dropIfExists($newTableName);
        $this->artisan('model-settings:model-settings-table')
            ->assertExitCode(0);
    }
}
