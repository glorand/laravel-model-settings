<?php

namespace Glorand\Model\Settings\Tests;

use Glorand\Model\Settings\Models\ModelSettings;
use Glorand\Model\Settings\Tests\Models\UsersWithDefaultSettingsTable;
use Glorand\Model\Settings\Tests\Models\UsersWithTable as User;
use Glorand\Model\Settings\Traits\HasSettingsTable;

class TableSettingsManagerTest extends TestCase
{
    /** @var \Glorand\Model\Settings\Tests\Models\UsersWithTable */
    private $model;
    /** @var array */
    protected $testArray = [
        'user' => [
            'first_name' => "John",
            'last_name'  => "Doe",
            'email'      => "john@doe.com",
        ],
    ];
    /** @var array */
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
        $this->assertTrue(array_key_exists(HasSettingsTable::class, $traits));
    }

    /**
     * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
     */
    public function testEmpty()
    {
        $this->model->settings()->clear();
        $this->assertTrue($this->model->settings()->empty());
        $this->model->settings()->apply($this->testArray);
        $this->assertFalse($this->model->settings()->empty());
    }

    /**
     * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
     */
    public function testExist()
    {
        $this->assertFalse($this->model->settings()->exist());
        $this->model->settings()->apply($this->testArray);
        $this->assertTrue($this->model->settings()->exist());
    }

    /**
     * @throws \Exception
     */
    public function testAll()
    {
        $this->assertEquals($this->model->settings()->all(), []);
    }

    /**
     * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
     * @throws \Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function testDefaultValue()
    {
        $this->model->defaultSettings = $this->defaultSettingsTestArray;
        $this->assertEquals(
            $this->defaultSettingsTestArray,
            $this->model->settings()->all()
        );

        $this->model->settings()->apply($this->testArray);
        $this->assertEquals(
            array_merge($this->defaultSettingsTestArray, $this->testArray),
            $this->model->settings()->all()
        );

        $this->assertTrue(cache()->has($this->model->getSettingsCacheKey()));

        $this->assertEquals(
            $this->testArray,
            cache()->get($this->model->getSettingsCacheKey())
        );

        $this->assertTrue(config('model_settings.settings_table_use_cache'));
        config()->set('model_settings.settings_table_use_cache', false);
        $this->assertFalse(config('model_settings.settings_table_use_cache'));
        $this->assertEquals(
            array_merge($this->defaultSettingsTestArray, $this->testArray),
            $this->model->settings()->all()
        );
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

        /*$testData = User::with(['modelSettings' => function (MorphOne $builder) {
            $builder->where(DB::raw("json_extract(settings, '$.user.first_name')"), 'JohnL');
        }])->whereHas('modelSettings')->first();
        $this->assertNull($testData->modelSettings);

        $testData = User::with(['modelSettings' => function (MorphOne $builder) {
            $builder->where(DB::raw("json_extract(settings, '$.user.first_name')"), 'John');
        }])->whereHas('modelSettings')->first();
        $this->assertNotNull($testData->modelSettings);*/


        /*$countJohnUsers = User::whereHas('modelSettings', function ($builder) {
            $builder->where(DB::raw("json_extract(settings, '$.user.first_name')"), 'John');
        }
        )->count();
        $this->assertEquals(1, $countJohnUsers);

        $countJohnUsers = User::whereHas('modelSettings', function ($builder) {
            $builder->where(DB::raw("json_extract(settings, '$.user.first_name')"), 'JohnL');
        }
        )->count();
        $this->assertEquals(0, $countJohnUsers);*/
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
        $this->assertEquals($this->model->settings()->all(), $this->testArray);
    }

    /**
     * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
     */
    public function testDelete()
    {
        $this->model->settings()->apply($this->testArray);
        $this->assertEquals(1, $this->model->modelSettings()->count());

        $this->assertEquals($this->model->settings()->all(), $this->testArray);
        $this->assertEquals($this->model->settings()->get('user.first_name'), 'John');

        $this->model->settings()->delete('user.first_name');
        $this->assertEquals($this->model->settings()->get('user.first_name'), null);

        $this->model->settings()->delete();
        $this->assertEquals($this->model->settings()->all(), []);
        $this->assertEquals(0, $this->model->modelSettings()->count());
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
        $this->assertEquals(0, $this->model->modelSettings()->count());
        $this->assertEquals($this->model->settings()->all(), []);
        $this->model->settings()->apply($this->testArray);
        $this->assertEquals($this->model->settings()->all(), $this->testArray);
        $this->assertEquals(1, $this->model->modelSettings()->count());

        $this->model->settings()->clear();
        $this->assertEquals(0, $this->model->modelSettings()->count());
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
