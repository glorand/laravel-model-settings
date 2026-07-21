<?php

namespace Glorand\Model\Settings\Tests;

use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Tests\Models\UsersWithParentModelWithField;
use Illuminate\Contracts\Container\BindingResolutionException;

final class ParentChildSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->model = UsersWithParentModelWithField::first();
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testSettingsForChild(): void
    {
        $testArray = ['a' => 'b'];
        $this->model->settings = $testArray;
        $this->model->save();
        $this->assertEquals($this->model->settings()->all(), $testArray);
    }
}
