<?php

namespace Glorand\Model\Settings\Tests;

use Glorand\Model\Settings\Tests\Models\UserWithField as User;
use Glorand\Model\Settings\Traits\HasSettingsField;

class FieldSettingsManagerTest extends TestCase
{
    /** @var \Glorand\Model\Settings\Tests\Models\UserWithField */
    protected $model;
    /** @var array */
    protected $testArray = [
        'user' => [
            'first_name' => "John",
            'last_name'  => "Doe",
            'email'      => "john@doe.com",
        ],
    ];
    /** @var array  */
    protected $defaultSettingsTestArray = [
        'project' => 'Main Project',
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->model = User::first();
    }

    public function testInit()
    {
        $traits = class_uses($this->model);
        $this->assertTrue(array_key_exists(HasSettingsField::class, $traits));
    }

    /**
     * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
     */
    public function testAll()
    {
        $this->assertEquals($this->model->settings()->all(), []);
    }

    /**
     * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
     */
    public function testDefaultValue()
    {
        $this->model->defaultSettings = $this->defaultSettingsTestArray;
        $this->assertEquals($this->defaultSettingsTestArray, $this->model->settings()->all());

        $this->model->settings()->apply($this->testArray);
        $this->assertEquals($this->model->settings()->all(), array_merge($this->defaultSettingsTestArray, $this->testArray));
    }

    /**
     * @expectedException \Glorand\Model\Settings\Exceptions\ModelSettingsException
     * @expectedExceptionMessage Unknown field
     */
    public function testSettingsMissingSettingsField()
    {
        $this->model->settingsFieldName = 'test';
        $this->model->settings()->all();
    }

    /**
     * @throws \Exception
     */
    public function testHas()
    {
        $this->model->settings()->apply($this->testArray);
        $this->assertEquals($this->model->settings()->all(), $this->testArray);

        $this->assertTrue($this->model->settings()->has('user.first_name'));
        $this->assertFalse($this->model->settings()->has('user.age'));
    }

    /**
     * @throws \Exception
     */
    public function testGet()
    {
        $this->assertEquals($this->model->settings()->all(), []);
        $this->assertEquals($this->model->settings()->get('user'), null);
        $this->model->settings()->apply($this->testArray);
        $this->assertEquals($this->model->settings()->get('user.first_name'), 'John');
    }

    /**
     * @throws \Exception
     */
    public function testGetMultiple()
    {
        $this->assertEquals($this->model->settings()->all(), []);
        $values = $this->model->settings()->getMultiple(['user.first_name', 'user.last_name'], 'def_val');
        $this->assertEquals(
            $values,
            [
                'user.first_name' => 'def_val',
                'user.last_name'  => 'def_val',
            ]
        );

        $this->model->settings()->apply($this->testArray);
        $values = $this->model->settings()->getMultiple(
            ['user.first_name', 'user.last_name', 'user.middle_name'],
            'def_val'
        );
        $this->assertEquals(
            $values,
            [
                'user.first_name'  => 'John',
                'user.last_name'   => 'Doe',
                'user.middle_name' => 'def_val',
            ]
        );
    }

    /**
     * @throws \Exception
     */
    public function testApply()
    {
        $this->model->settings()->apply($this->testArray);
        $this->assertEquals($this->model->fresh()->settings()->all(), $this->testArray);
    }

    public function testPersistence()
    {
        $this->model->settings()->apply($this->testArray);
        $this->assertEquals($this->model->fresh()->settings()->all(), $this->testArray);

        $this->model->settings()->delete();

        $this->model->setPersistSettings(false);
        $this->model->settings()->apply($this->testArray);
        $this->assertEquals($this->model->fresh()->settings()->all(), []);

        $this->model->setPersistSettings(false);
        $this->model->settings()->apply($this->testArray);
        $this->model->save();
        $this->assertEquals($this->model->fresh()->settings()->all(), $this->testArray);

        $this->model->settings()->delete();

        $this->model->fresh();
        $this->model->setPersistSettings(true);
        $this->model->settings()->apply($this->testArray);
        $this->assertEquals($this->model->fresh()->settings()->all(), $this->testArray);
    }

    /**
     * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
     */
    public function testDelete()
    {
        $this->model->settings()->apply($this->testArray);

        $this->assertEquals($this->model->settings()->all(), $this->testArray);
        $this->assertEquals($this->model->settings()->get('user.first_name'), 'John');

        $this->model->settings()->delete('user.first_name');
        $this->assertEquals($this->model->settings()->get('user.first_name'), null);

        $this->model->settings()->delete();
        $this->assertEquals($this->model->settings()->all(), []);
    }

    /**
     * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
     */
    public function testDeleteMultiple()
    {
        $this->model->settings()->apply($this->testArray);
        $this->assertEquals($this->model->settings()->all(), $this->testArray);

        $this->model->settings()->deleteMultiple(['user.first_name', 'user.last_name']);
        $testData = $this->model->settings()->get('user');
        $this->assertArrayNotHasKey('first_name', $testData);
        $this->assertArrayNotHasKey('last_name', $testData);
        $this->assertArrayHasKey('email', $testData);
    }

    /**
     * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
     */
    public function testClear()
    {
        $this->model->settings()->apply($this->testArray);
        $this->assertEquals($this->model->settings()->all(), $this->testArray);

        $this->model->settings()->clear();
        $this->assertEquals($this->model->settings()->all(), []);
    }

    /**
     * @throws \Exception
     */
    public function testSet()
    {
        $this->assertEquals($this->model->settings()->all(), []);

        $this->model->settings()->set('user.age', 18);
        $this->assertEquals($this->model->settings()->all(), ['user' => ['age' => 18]]);
    }

    /**
     * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
     */
    public function testSetMultiple()
    {
        $this->assertEquals($this->model->settings()->all(), []);
        $testData = [
            'a' => 'a',
            'b' => 'b',
        ];
        $this->model->settings()->setMultiple($testData);
        $this->assertEquals($this->model->settings()->all(), $testData);

        $this->model->settings()->setMultiple($this->testArray);
        $this->assertEquals($this->model->settings()->all(), array_merge($testData, $this->testArray));
    }

    /**
     * @throws \Exception
     */
    public function testUpdate()
    {
        $this->assertEquals($this->model->settings()->all(), []);

        $this->model->settings()->set('user.age', 18);
        $this->assertEquals($this->model->settings()->all(), ['user' => ['age' => 18]]);

        $this->model->settings()->update('user.age', 19);
        $this->assertEquals($this->model->settings()->all(), ['user' => ['age' => 19]]);
    }
}
