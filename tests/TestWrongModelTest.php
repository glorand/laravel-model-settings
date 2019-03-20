<?php

namespace Glorand\Model\Settings\Tests;

use Glorand\Model\Settings\Managers\FieldSettingsManager;
use Glorand\Model\Settings\Tests\Models\WrongUser;
use Glorand\Model\Settings\Tests\Models\WrongUserWithField;

class TestWrongModelTest extends TestCase
{
    /**
     * @throws \Exception
     * @expectedException \Glorand\Model\Settings\Exceptions\ModelSettingsException
     * @expectedExceptionMessage missing HasSettings
     */
    public function testSettingsFieldUndefined()
    {
        new FieldSettingsManager(WrongUser::first());
    }

    /**
     * @expectedException \Glorand\Model\Settings\Exceptions\ModelSettingsException
     * @expectedExceptionMessage Unknown field
     */
    public function testSettingsMissingSettingsField()
    {
        $model = WrongUserWithField::first();
        $model->settings()->all();
    }
}
