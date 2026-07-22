<?php

namespace Glorand\Model\Settings\Tests;

use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Tests\Models\UserWithField as User;
use Illuminate\Contracts\Container\BindingResolutionException;

final class FieldSettingsManagerTest extends TestCase
{
    protected User $model;
    protected array $testArray = [
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
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testModelArraySettings(): void
    {
        $testArray = ['a' => 'b'];
        $this->model->settings = $testArray;
        $this->model->save();
        $this->assertEquals($this->model->settings()->all(), $testArray);
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
        $this->assertEquals($this->model->fresh()->settings()->all(), $this->testArray);
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testPartialSelectThrowsInsteadOfSilentlyWipingSettings(): void
    {
        $this->model->settings()->apply($this->testArray);

        $partial = User::select('id')->find($this->model->getKey());

        $this->expectException(ModelSettingsException::class);
        $this->expectExceptionMessage('was not selected');
        $partial->settings()->set('user.age', 18);
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testSchemaCheckCacheIsScopedPerFieldName(): void
    {
        // Warm the "has_field" cache for the real field name on this table.
        $this->model->settings()->all();

        // A fresh instance, not a clone: settings() memoizes its manager per-instance,
        // so a clone would keep reusing the original's manager and field name.
        $other = User::find($this->model->getKey());
        $other->settingsFieldName = 'nonexistent_field';

        $this->expectException(ModelSettingsException::class);
        $this->expectExceptionMessage('Unknown field');
        $other->settings()->all();
    }
}
