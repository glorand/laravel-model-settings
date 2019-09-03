<?php

namespace Glorand\Model\Settings\Tests;

use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Managers\FieldSettingsManager;
use Glorand\Model\Settings\Tests\Models\WrongUser;
use Glorand\Model\Settings\Tests\Models\WrongUserWithField;

class TestWrongModelTest extends TestCase
{
    /**
     * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
     */
    public function testSettingsFieldUndefined()
    {
        $this->expectException(ModelSettingsException::class);
        $this->expectExceptionMessage('missing HasSettings');
        new FieldSettingsManager(WrongUser::first());
    }

    public function testSettingsMissingSettingsField()
    {
        $this->expectException(ModelSettingsException::class);
        $this->expectExceptionMessage('Unknown field');
        $model = WrongUserWithField::first();
        $model->settings()->all();
    }
}
