<?php

namespace Glorand\Model\Settings\Tests;

use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Managers\FieldSettingsManager;
use Glorand\Model\Settings\Tests\Models\WrongUser;
use Glorand\Model\Settings\Tests\Models\WrongUserWithField;

final class TestWrongModelTest extends TestCase
{
    /**
     * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
     */
    public function testSettingsFieldUndefined(): void
    {
        $this->expectException(ModelSettingsException::class);
        $this->expectExceptionMessage('missing HasSettings');
        new FieldSettingsManager(WrongUser::first());
    }

    public function testSettingsMissingSettingsField(): void
    {
        $this->expectException(ModelSettingsException::class);
        $this->expectExceptionMessage('Unknown field');
        $model = WrongUserWithField::first();
        $model->settings()->all();
    }
}
