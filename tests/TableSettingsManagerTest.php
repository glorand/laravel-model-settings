<?php

namespace Glorand\Model\Settings\Tests;

use Glorand\Model\Settings\Models\ModelSettings;
use Glorand\Model\Settings\Tests\Models\UsersWithTable as User;

class TableSettingsManagerTest extends TestCase
{
    /** @var \Glorand\Model\Settings\Tests\Models\UsersWithTable */
    private $model;
    /** @var array */
    protected $testArray = [
        'user' => [
            'first_name' => "John",
            'last_name'  => "Doe",
            'email'      => "john@doe.com",
        ],
    ];
    /** @var array */
    protected $defaultSettingsTestArray = [
        'project' => 'Main Project',
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->model = User::first();
    }

    /**
     * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
     * @throws \Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function testSpecificDefaultValue()
    {
        $this->model->defaultSettings = $this->defaultSettingsTestArray;
        $this->assertEquals(
            $this->defaultSettingsTestArray,
            $this->model->settings()->all()
        );

        $this->model->settings()->apply($this->testArray);
        $this->assertEquals(
            array_merge($this->defaultSettingsTestArray, $this->testArray),
            $this->model->settings()->all()
        );

        $this->assertTrue(cache()->has($this->model->getSettingsCacheKey()));

        $this->assertEquals(
            $this->testArray,
            cache()->get($this->model->getSettingsCacheKey())
        );

        $this->assertTrue(config('model_settings.settings_table_use_cache'));
        config()->set('model_settings.settings_table_use_cache', false);
        $this->assertFalse(config('model_settings.settings_table_use_cache'));
        $this->assertEquals(
            array_merge($this->defaultSettingsTestArray, $this->testArray),
            $this->model->settings()->all()
        );
    }

    public function testSettingsTableCount()
    {
        $this->model->settings()->apply($this->testArray);
        $this->assertEquals(1, ModelSettings::all()->count());
        $this->model->settings()->apply($this->testArray);
        $this->assertEquals(1, ModelSettings::all()->count());

        $this->assertEquals($this->testArray, $this->model->settings()->all());
        $this->assertEquals(1, $this->model->modelSettings()->count());

        $this->model->settings()->delete();
        $this->assertEquals([], $this->model->settings()->all());
        $this->assertEquals(0, $this->model->modelSettings()->count());

        $this->model->settings()->apply($this->testArray);
        $this->assertEquals($this->testArray, $this->model->settings()->all());
        $this->assertEquals(1, $this->model->modelSettings()->count());

        $this->model->settings()->clear();
        $this->assertEquals([], $this->model->settings()->all());
        $this->assertEquals(0, $this->model->modelSettings()->count());
    }
}
