<?php

namespace Glorand\Model\Settings\Tests;

use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Models\ModelSettings;
use Glorand\Model\Settings\Tests\Models\UsersWithTable as User;

final class TableSettingsManagerTest extends TestCase
{
    private User $model;
    protected array $testArray = [
        'user' => [
            'first_name' => "John",
            'last_name'  => "Doe",
            'email'      => "john@doe.com",
        ],
    ];
    protected array $defaultSettingsTestArray = [
        'project' => 'Main Project',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = User::first();
    }

    public function testUnsavedModelThrows(): void
    {
        $this->expectException(ModelSettingsException::class);
        $this->expectExceptionMessage('must be saved');

        (new User())->settings()->all();
    }

    /**
     * @throws ModelSettingsException
     */
    public function testSpecificDefaultValue(): void
    {
        $this->model->defaultSettings = $this->defaultSettingsTestArray;
        $this->assertEquals(
            $this->defaultSettingsTestArray,
            $this->model->settings()->all()
        );

        $this->assertEquals(
            $this->model->config()->all(),
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

        $this->assertTrue(config('model_settings.drivers.table.use_cache'));
        config()->set('model_settings.drivers.table.use_cache', false);
        $this->assertFalse(config('model_settings.drivers.table.use_cache'));
        $this->assertEquals(
            array_merge($this->defaultSettingsTestArray, $this->testArray),
            $this->model->settings()->all()
        );
    }

    public function testSettingsTableCount(): void
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

    public function testAddEagerConstraints(): void
    {
        $this->model->settings()->apply($this->testArray);
        $this->assertEquals(1, ModelSettings::all()->count());

        $this->expectExceptionMessage('addEagerConstraints');
        $this->model->load('settings')->settings()->set('test', 'test');

        $this->model->load('modelSettings')->settings()->set('test', 'test');
    }
}
