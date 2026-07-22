<?php

namespace Glorand\Model\Settings\Tests;

use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Tests\Models\UserWithArrayCastField as User;
use Illuminate\Contracts\Container\BindingResolutionException;

final class FieldSettingsManagerArrayCastTest extends TestCase
{
    protected User $model;

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->model = User::first();
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testReadingPreExistingRawJsonDoesNotCrash(): void
    {
        $this->model->getConnection()->table('users_with_field')
            ->where('id', $this->model->getKey())
            ->update(['settings' => json_encode(['a' => 'b'])]);

        $fresh = User::find($this->model->getKey());
        $this->assertEquals(['a' => 'b'], $fresh->settings()->all());
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testPersistenceRoundTripStoresCleanJson(): void
    {
        $testArray = ['user' => ['first_name' => 'John'], 'project' => ['name' => 'Project One']];

        $this->model->settings()->apply($testArray);

        $this->assertEquals($testArray, $this->model->fresh()->settings()->all());

        $rawColumnValue = $this->model->getConnection()->table('users_with_field')
            ->where('id', $this->model->getKey())
            ->value('settings');

        $this->assertEquals($testArray, json_decode($rawColumnValue, true));
    }
}
