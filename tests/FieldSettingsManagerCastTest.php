<?php

namespace Glorand\Model\Settings\Tests;

use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Tests\Models\UserWithAsArrayObjectCastField;
use Glorand\Model\Settings\Tests\Models\UserWithAsCollectionCastField;
use Glorand\Model\Settings\Tests\Models\UserWithObjectCastField;
use Illuminate\Contracts\Container\BindingResolutionException;

final class FieldSettingsManagerCastTest extends TestCase
{
    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testObjectCastRoundTrips(): void
    {
        $model = UserWithObjectCastField::first();

        $model->settings()->apply(['a' => 'b']);

        $fresh = UserWithObjectCastField::find($model->getKey());
        $this->assertEquals(['a' => 'b'], $fresh->settings()->all());
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testAsCollectionClassCastRoundTrips(): void
    {
        $model = UserWithAsCollectionCastField::first();

        $model->settings()->apply(['a' => 'b']);

        $rawColumnValue = $model->getConnection()->table('users_with_field')
            ->where('id', $model->getKey())
            ->value('settings');
        $this->assertEquals(['a' => 'b'], json_decode($rawColumnValue, true));

        $fresh = UserWithAsCollectionCastField::find($model->getKey());
        $this->assertEquals(['a' => 'b'], $fresh->settings()->all());
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testAsArrayObjectClassCastRoundTrips(): void
    {
        $model = UserWithAsArrayObjectCastField::first();

        $model->settings()->apply(['a' => 'b']);

        $rawColumnValue = $model->getConnection()->table('users_with_field')
            ->where('id', $model->getKey())
            ->value('settings');
        $this->assertEquals(['a' => 'b'], json_decode($rawColumnValue, true));

        $fresh = UserWithAsArrayObjectCastField::find($model->getKey());
        $this->assertEquals(['a' => 'b'], $fresh->settings()->all());
    }
}
