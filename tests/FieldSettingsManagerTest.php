<?php

namespace Glorand\Model\Settings\Tests;

use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Tests\Models\UserWithField as User;

final class FieldSettingsManagerTest extends TestCase
{
    /** @var \Glorand\Model\Settings\Tests\Models\UserWithField */
    protected $model;
    /** @var array */
    protected $testArray = [
        'user'    => [
            'first_name' => "John",
            'last_name'  => "Doe",
            'email'      => "john@doe.com",
        ],
        'project' => [
            'name'        => 'Project One',
            'description' => 'Test Description',
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = User::first();
    }

    /**
     * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
     */
    public function testModelArraySettings(): void
    {
        $testArray = ['a' => 'b'];
        $this->model->settings = $testArray;
        $this->model->save();
        $this->assertEquals($this->model->settings()->all(), $testArray);
    }

    public function testSettingsMissingSettingsField(): void
    {
        $this->expectException(ModelSettingsException::class);
        $this->expectExceptionMessage('Unknown field');
        $this->model->settingsFieldName = 'test';
        $this->model->settings()->all();
    }

    /**
     * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
     */
    public function testPersistence(): void
    {
        $this->model->settings()->apply($this->testArray);
        $this->assertEquals($this->testArray, $this->model->fresh()->settings()->all());

        $this->model->settings()->delete();

        $this->model->setPersistSettings(false);
        $this->model->settings()->apply($this->testArray);
        $this->assertEquals([], $this->model->fresh()->settings()->all());

        $this->model->setPersistSettings(false);
        $this->model->settings()->apply($this->testArray);
        $this->model->save();
        $this->assertEquals($this->testArray, $this->model->fresh()->settings()->all());

        $this->model->settings()->delete();

        $this->model->fresh();
        $this->model->setPersistSettings(true);
        $this->model->settings()->apply($this->testArray);
        $this->assertEquals($this->model->fresh()->settings()->all(), $this->testArray);
    }
}
