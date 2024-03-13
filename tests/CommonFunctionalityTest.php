<?php

namespace Glorand\Model\Settings\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use Glorand\Model\Settings\Traits\HasSettingsField;
use Glorand\Model\Settings\Traits\HasSettingsRedis;
use Glorand\Model\Settings\Traits\HasSettingsTable;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;
use Lunaweb\RedisMock\MockPredisConnection;

final class CommonFunctionalityTest extends TestCase
{
    /** @var string[] */
    protected static array $modelTypes = [
        'field',
        'text_field',
        'table',
        'redis',
    ];
    /** @var \string[][] */
    protected static array $testArray = [
        'user' => [
            'first_name' => "John",
            'last_name' => "Doe",
            'email' => "john@doe.com",
            'age' => 27,
        ],
        'project' => [
            'name' => 'Project One',
            'description' => 'Test Description',
        ],
    ];

    /** @var array */
    protected $defaultSettingsTestArray = [
        'config' => [
            'email' => 'gmail',
            'file' => 'aws',
        ],
    ];

    /** @var string[] */
    protected $rules = [
        'user' => [
            'array',
        ],
        'user.email' => [
            'string',
            'email',
        ],
        'user.age' => 'integer',
    ];

    protected function setUp(): void
    {
        parent::setUp();
    }

    public static function modelTypesProvider(): array
    {
        $modelTypes = [];
        foreach (self::$modelTypes as $modelType) {
            $modelTypes[$modelType] = [$modelType];
        }

        return $modelTypes;
    }

    public function testInit(): void
    {
        $traits = class_uses($this->getModelByType('redis'));
        $this->assertTrue(array_key_exists(HasSettingsRedis::class, $traits));

        $traits = class_uses($this->getModelByType('field'));
        $this->assertArrayHasKey(HasSettingsField::class, $traits);

        $traits = class_uses($this->getModelByType('text_field'));
        $this->assertArrayHasKey(HasSettingsField::class, $traits);

        $traits = class_uses($this->getModelByType('table'));
        $this->assertTrue(array_key_exists(HasSettingsTable::class, $traits));

        $this->assertInstanceOf(MockPredisConnection::class, Redis::connection());
    }

    #[DataProvider('modelTypesProvider')]
    public function testEmpty(string $modelType): void
    {
        $model = $this->getModelByType($modelType);

        $this->assertTrue($model->settings()->clear()->empty());
        $this->assertFalse($model->settings()->apply(self::$testArray)->empty());
    }

    #[DataProvider('modelTypesProvider')]
    public function testExist(string $modelType): void
    {
        $model = $this->getModelByType($modelType);

        $this->assertFalse($model->settings()->clear()->exist());
        $this->assertTrue($model->settings()->apply(self::$testArray)->exist());
    }

    #[DataProvider('modelTypesProvider')]
    public function testHas(string $modelType): void
    {
        $model = $this->getModelByType($modelType);

        $this->assertEquals(
            self::$testArray,
            $model->settings()->apply(self::$testArray)->all()
        );

        $this->assertTrue($model->settings()->has('user.first_name'));
        $this->assertFalse($model->settings()->has('user.role'));
    }

    #[DataProvider('modelTypesProvider')]
    public function testAll(string $modelType): void
    {
        $model = $this->getModelByType($modelType);

        $this->assertEquals(
            [],
            $model->settings()->clear()->all()
        );

        $this->assertEquals(
            self::$testArray,
            $model->settings()->apply(self::$testArray)->all()
        );
    }

    #[DataProvider('modelTypesProvider')]
    public function testGet(string $modelType): void
    {
        $model = $this->getModelByType($modelType);
        $model->settings()->clear();
        $this->assertEquals([], $model->settings()->all());
        $this->assertEquals(null, $model->settings()->get('user'));
        $model->settings()->apply(self::$testArray);
        $this->assertEquals('John', $model->settings()->get('user.first_name'));
    }

    #[DataProvider('modelTypesProvider')]
    public function testGetMultiple(string $modelType): void
    {
        $model = $this->getModelByType($modelType);

        $model->settings()->clear();
        $this->assertEquals([], $model->settings()->all());
        $values = $model->settings()->getMultiple(['user.first_name', 'user.last_name'], 'def_val');
        $this->assertEquals(
            [
                'user' => [
                    'first_name' => 'def_val',
                    'last_name' => 'def_val',
                ],
            ],
            $values
        );

        $model->settings()->apply(self::$testArray);
        $values = $model->settings()->getMultiple(
            ['user', 'project.name', 'date'],
            'def_val'
        );
        $this->assertEquals(
            [
                'user' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john@doe.com',
                    'age' => 27,
                ],
                'project' => [
                    'name' => 'Project One',
                ],
                'date' => 'def_val',
            ],
            $values
        );
    }

    #[DataProvider('modelTypesProvider')]
    public function testApply(string $modelType): void
    {
        $model = $this->getModelByType($modelType);
        $model->settings()->apply(self::$testArray);
        $this->assertEquals(self::$testArray, $model->fresh()->settings()->all());
    }

    #[DataProvider('modelTypesProvider')]
    public function testUpdate(string $modelType): void
    {
        $model = $this->getModelByType($modelType);

        $model->settings()->clear();
        $this->assertEquals([], $model->settings()->all());

        $model->settings()->set('user.age', 18);
        $this->assertEquals(['user' => ['age' => 18]], $model->settings()->all());

        $model->settings()->update('user.age', 19);
        $this->assertEquals(['user' => ['age' => 19]], $model->settings()->all());
    }

    #[DataProvider('modelTypesProvider')]
    public function testSet(string $modelType): void
    {
        $model = $this->getModelByType($modelType);

        $model->settings()->clear();
        $this->assertEquals([], $model->settings()->all());

        $model->settings()->set('user.age', 18);
        $this->assertEquals(['user' => ['age' => 18]], $model->settings()->all());
    }

    #[DataProvider('modelTypesProvider')]
    public function testSetMultiple(string $modelType): void
    {
        $model = $this->getModelByType($modelType);

        $model->settings()->clear();
        $this->assertEquals([], $model->settings()->all());
        $testData = [
            'a' => 'a',
            'b' => 'b',
        ];
        $model->settings()->setMultiple($testData);
        $this->assertEquals($model->settings()->all(), $testData);

        $model->settings()->setMultiple(self::$testArray);
        $this->assertEquals(
            array_merge($testData, self::$testArray),
            $model->settings()->all()
        );
    }

    #[DataProvider('modelTypesProvider')]
    public function testClear(string $modelType): void
    {
        $model = $this->getModelByType($modelType);

        $model->settings()->clear()->apply(self::$testArray);
        $this->assertEquals(self::$testArray, $model->settings()->all());

        $model->settings()->clear();
        $this->assertEquals([], $model->settings()->all());
    }

    #[DataProvider('modelTypesProvider')]
    public function testDelete(string $modelType): void
    {
        $model = $this->getModelByType($modelType);
        $model->settings()->apply(self::$testArray);

        $this->assertEquals(self::$testArray, $model->settings()->all());
        $this->assertEquals('John', $model->settings()->get('user.first_name'));

        $model->settings()->delete('user.first_name');
        $this->assertEquals(null, $model->settings()->get('user.first_name'));

        $model->settings()->delete();
        $this->assertEquals([], $model->settings()->all());
    }

    #[DataProvider('modelTypesProvider')]
    public function testDeleteMultiple(string $modelType): void
    {
        $model = $this->getModelByType($modelType);
        $model->settings()->apply(self::$testArray);
        $this->assertEquals(self::$testArray, $model->settings()->all());

        $model->settings()->deleteMultiple(['user.first_name', 'user.last_name']);
        $testData = $model->settings()->get('user');
        $this->assertArrayNotHasKey('first_name', $testData);
        $this->assertArrayNotHasKey('last_name', $testData);
        $this->assertArrayHasKey('email', $testData);
    }

    #[DataProvider('modelTypesProvider')]
    public function testDefaultValue(string $modelType): void
    {
        $model = $this->getModelByType($modelType);
        $model->settings()->clear();
        $model->defaultSettings = $this->defaultSettingsTestArray;
        $this->assertEquals($this->defaultSettingsTestArray, $model->settings()->all());

        $model->settings()->apply(self::$testArray);
        $this->assertEquals(
            array_merge($this->defaultSettingsTestArray, self::$testArray),
            $model->settings()->all()
        );

        $model->settings()->clear();

        $default = [
            'a' => [
                'val_a_1' => 1,
                'val_a_2' => 2,
            ],
        ];
        $applyData = [
            'a' => [
                'val_a_2' => '2-updated',
                'val_a_3' => 3,
            ],
            'b' => 'b-val',
        ];

        $model->defaultSettings = $default;
        $this->assertEquals($default, $model->settings()->all());

        $model->settings()->apply($applyData);
        $this->assertEquals(
            [
                'a' => [
                    'val_a_1' => 1,
                    'val_a_2' => '2-updated',
                    'val_a_3' => 3,
                ],
                'b' => 'b-val',
            ],
            $model->settings()->all()
        );
    }

    #[DataProvider('modelTypesProvider')]
    public function testDefaultValueFromConfig(string $modelType): void
    {
        $model = $this->getModelByType($modelType);

        $model->defaultSettings = false;
        $model->settings()->clear();
        $this->assertEquals([], $model->settings()->all());

        config()->set('model_settings.defaultSettings.' . $model->getTable(), $this->defaultSettingsTestArray);

        $this->assertEquals($this->defaultSettingsTestArray, $model->settings()->all());
        $model->settings()->apply(self::$testArray);
        $this->assertEquals(
            array_merge($this->defaultSettingsTestArray, self::$testArray),
            $model->settings()->all()
        );
    }

    #[DataProvider('modelTypesProvider')]
    public function testValidateData(string $modelType): void
    {
        $model = $this->getModelByType($modelType);
        $model->settingsRules = $this->rules;

        $model->settings()->clear();
        $this->assertEquals([], $model->settings()->all());

        $model->settings()->apply(self::$testArray);

        $model->settings()->clear();
        $this->assertEquals([], $model->settings()->all());

        try {
            $model->settings()->set('user.age', 'string');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('user.age', $e->errors());
        }

        $testArray = self::$testArray;
        $testArray['user']['age'] = 'string';
        $this->expectException(ValidationException::class);
        $model->settings()->apply($testArray);
    }
}
