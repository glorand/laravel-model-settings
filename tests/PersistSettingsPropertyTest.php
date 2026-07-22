<?php

namespace Glorand\Model\Settings\Tests;

use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Tests\Models\UserWithPersistSettingsProperty as User;
use Illuminate\Contracts\Container\BindingResolutionException;

final class PersistSettingsPropertyTest extends TestCase
{
    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testModelCanRedeclarePersistSettingsPropertyWithoutFatalError(): void
    {
        $model = User::first();

        $this->assertFalse($model->isPersistSettings());

        $model->settings()->apply(['a' => 'b']);
        $this->assertEquals([], $model->fresh()->settings()->all());

        $model->save();
        $this->assertEquals(['a' => 'b'], $model->fresh()->settings()->all());
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testSetPersistSettingsOverridesTheDeclaredProperty(): void
    {
        $model = User::first();

        $model->setPersistSettings(true);
        $this->assertTrue($model->isPersistSettings());

        $model->settings()->apply(['a' => 'b']);
        $this->assertEquals(['a' => 'b'], $model->fresh()->settings()->all());
    }
}
