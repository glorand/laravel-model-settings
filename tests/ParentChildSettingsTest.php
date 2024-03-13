<?php

namespace Glorand\Model\Settings\Tests;

use Glorand\Model\Settings\Tests\Models\UsersWithParentModelWithField;
use Glorand\Model\Settings\Tests\Models\UserWithField as User;

final class ParentChildSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->model = UsersWithParentModelWithField::first();
    }

    public function testSettingsForChild(): void
    {
        $testArray = ['a' => 'b'];
        $this->model->settings = $testArray;
        $this->model->save();
        $this->assertEquals($this->model->settings()->all(), $testArray);
    }
}
