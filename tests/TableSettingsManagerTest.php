<?php

namespace Glorand\Model\Settings\Tests;

use Glorand\Model\Settings\Models\ModelSettings;
use Glorand\Model\Settings\Tests\Models\UsersWithTable as User;
use Glorand\Model\Settings\Traits\HasSettingsTable;

class TableSettingsManagerTest extends TestCase
{
    /** @var array */
    protected $testArray = [
        'user' => [
            'first_name' => "John",
            'last_name'  => "Doe",
        ],
    ];
    /** @var \Glorand\Model\Settings\Tests\Models\UsersWithTable */
    private $model;

    public function setUp(): void
    {
        parent::setUp();
        $this->model = User::first();
    }

    public function testInit()
    {
        $traits = class_uses($this->model);
        $this->assertTrue(array_key_exists(HasSettingsTable::class, $traits));
    }

    /**
     * @throws \Exception
     */
    public function testAll()
    {
        $this->assertEquals($this->model->settings()->all(), []);
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

        $this->assertEquals(1, ModelSettings::all()->count());
        $this->model->settings()->apply($this->testArray);
        $this->assertEquals(1, ModelSettings::all()->count());
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
    public function testApply()
    {
        $this->model->settings()->apply($this->testArray);
        $this->assertEquals($this->model->settings()->all(), $this->testArray);
    }

    /**
     * @throws \Exception
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
     * @throws \Exception
     */
    public function testSet()
    {
        $this->assertEquals($this->model->settings()->all(), []);

        $this->model->settings()->set('user.age', 18);
        $this->assertEquals($this->model->settings()->all(), ['user' => ['age' => 18]]);
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
