<?php

namespace Glorand\Model\Settings\Tests;

use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Tests\Models\UserWithTextField as User;
use Illuminate\Contracts\Container\BindingResolutionException;

final class TextFieldSettingsManagerTest extends TestCase
{
    protected User $model;
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

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testIfSettingsIsNotValidJson(): void
    {
        $this->model->settings = 'Invalid Json';
        $this->model->save();

        $this->assertEquals([], $this->model->settings()->all());
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testModelArraySettings(): void
    {
        $testArray = ['a' => 'b'];
        $this->model->settings = $testArray;
        $this->model->save();
        $this->assertEquals($testArray, $this->model->settings()->all());
    }

    /**
     * @throws BindingResolutionException
     */
    public function testSettingsMissingSettingsField(): void
    {
        $this->expectException(ModelSettingsException::class);
        $this->expectExceptionMessage('Unknown field');
        $this->model->settingsFieldName = 'test';
        $this->model->settings()->all();
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
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
        $this->model->setPersistSettings();
        $this->model->settings()->apply($this->testArray);
        $this->assertEquals($this->testArray, $this->model->fresh()->settings()->all());
    }
}
