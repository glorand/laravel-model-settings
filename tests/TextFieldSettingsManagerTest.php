<?php

namespace Glorand\Model\Settings\Tests;

use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Tests\Models\UserWithTextField as User;
use Glorand\Model\Settings\Traits\HasSettingsField;

class TextFieldSettingsManagerTest extends TestCase
{
	/** @var \Glorand\Model\Settings\Tests\Models\UserWithTextField */
	protected $model;
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
		$this->assertArrayHasKey(HasSettingsField::class, $traits);
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
	 * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
	 */
	public function testIfSettingsIsNotValidJson()
	{
		$this->model->settings = 'Invalid Json';
		$this->model->save();

		$this->assertEquals([], $this->model->settings()->all());
	}

	/**
	 * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
	 */
	public function testModelArraySettings()
	{
		$testArray             = ['a' => 'b'];
		$this->model->settings = $testArray;
		$this->model->save();
		$this->assertEquals($testArray, $this->model->settings()->all());
	}

	/**
	 * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
	 */
	public function testAll()
	{
		$this->assertEquals([], $this->model->settings()->all());
	}

	/**
	 * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
	 */
	public function testDefaultValue()
	{
		$this->model->defaultSettings = $this->defaultSettingsTestArray;
		$this->assertEquals($this->defaultSettingsTestArray, $this->model->settings()->all());

		$this->model->settings()->apply($this->testArray);
		$this->assertEquals(
			array_merge($this->defaultSettingsTestArray, $this->testArray),
			$this->model->settings()->all()
		);
	}

	public function testSettingsMissingSettingsField()
	{
		$this->expectException(ModelSettingsException::class);
		$this->expectExceptionMessage('Unknown field');
		$this->model->settingsFieldName = 'test';
		$this->model->settings()->all();
	}

	/**
	 * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
	 */
	public function testHas()
	{
		$this->model->settings()->apply($this->testArray);
		$this->assertEquals($this->testArray, $this->model->settings()->all());

		$this->assertTrue($this->model->settings()->has('user.first_name'));
		$this->assertFalse($this->model->settings()->has('user.age'));
	}

	/**
	 * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
	 */
	public function testGet()
	{
		$this->assertEquals([], $this->model->settings()->all());
		$this->assertEquals(null, $this->model->settings()->get('user'));
		$this->model->settings()->apply($this->testArray);
		$this->assertEquals('John', $this->model->settings()->get('user.first_name'));
	}

	/**
	 * @throws \Exception
	 */
	public function testGetMultiple()
	{
		$this->assertEquals([], $this->model->settings()->all());
		$values = $this->model->settings()->getMultiple(['user.first_name', 'user.last_name'], 'def_val');
		$this->assertEquals(
			[
				'user' => [
					'first_name' => 'def_val',
					'last_name'  => 'def_val'
				]
			],
			$values
		);

		$this->model->settings()->apply($this->testArray);
		$values = $this->model->settings()->getMultiple(
			['user.first_name', 'user.last_name', 'user.middle_name'],
			'def_val'
		);
		$this->assertEquals(
			[
				'user' => [
					'first_name'  => 'John',
					'last_name'   => 'Doe',
					'middle_name' => 'def_val',
				]
			],
			$values
		);
	}

	/**
	 * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
	 */
	public function testApply()
	{
		$this->model->settings()->apply($this->testArray);
		$this->assertEquals($this->model->fresh()->settings()->all(), $this->testArray);
	}

	/**
	 * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
	 */
	public function testPersistence()
	{
		$this->model->settings()->apply($this->testArray);
		$this->assertEquals($this->testArray, $this->model->fresh()->settings()->all());

		$this->model->settings()->delete();

		$this->model->setPersistSettings(false);
		$this->model->settings()->apply($this->testArray);
		$this->assertEquals([], $this->model->fresh()->settings()->all());

		$this->model->setPersistSettings(false);
		$this->model->settings()->apply($this->testArray);
		$this->model->save();
		$this->assertEquals($this->testArray, $this->model->fresh()->settings()->all());

		$this->model->settings()->delete();

		$this->model->fresh();
		$this->model->setPersistSettings(true);
		$this->model->settings()->apply($this->testArray);
		$this->assertEquals($this->testArray, $this->model->fresh()->settings()->all());
	}

	/**
	 * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
	 */
	public function testDelete()
	{
		$this->model->settings()->apply($this->testArray);

		$this->assertEquals($this->testArray, $this->model->settings()->all());
		$this->assertEquals('John', $this->model->settings()->get('user.first_name'));

		$this->model->settings()->delete('user.first_name');
		$this->assertEquals(null, $this->model->settings()->get('user.first_name'));

		$this->model->settings()->delete();
		$this->assertEquals([], $this->model->settings()->all());
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
		$this->assertEquals($this->testArray, $this->model->settings()->all());

		$this->model->settings()->clear();
		$this->assertEquals([], $this->model->settings()->all());
	}

	/**
	 * @throws \Exception
	 */
	public function testSet()
	{
		$this->assertEquals([], $this->model->settings()->all());

		$this->model->settings()->set('user.age', 18);
		$this->assertEquals(['user' => ['age' => 18]], $this->model->settings()->all());
	}

	/**
	 * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
	 */
	public function testSetMultiple()
	{
		$this->assertEquals([], $this->model->settings()->all());
		$testData = [
			'a' => 'a',
			'b' => 'b',
		];
		$this->model->settings()->setMultiple($testData);
		$this->assertEquals($testData, $this->model->settings()->all());

		$this->model->settings()->setMultiple($this->testArray);
		$this->assertEquals(array_merge($testData, $this->testArray), $this->model->settings()->all());
	}

	/**
	 * @throws \Exception
	 */
	public function testUpdate()
	{
		$this->assertEquals([], $this->model->settings()->all());

		$this->model->settings()->set('user.age', 18);
		$this->assertEquals(['user' => ['age' => 18]], $this->model->settings()->all());

		$this->model->settings()->update('user.age', 19);
		$this->assertEquals(['user' => ['age' => 19]], $this->model->settings()->all());
	}
}
