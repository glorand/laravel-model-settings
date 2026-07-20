<?php

namespace Glorand\Model\Settings\Tests;

use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Tests\Models\UserWithRedis as User;
use Illuminate\Support\Facades\Redis;

final class RedisSettingsManagerTest extends TestCase
{
    /** @var \Glorand\Model\Settings\Tests\Models\UserWithRedis */
    protected $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = User::first();
    }

    public function testMarker(): void
    {
        $this->assertTrue(true);
    }

    public function testClearRemovesRedisKey(): void
    {
        $this->model->settings()->apply(['a' => 'b']);
        $this->assertNotNull(Redis::connection()->get($this->model->cacheKey()));

        $this->model->settings()->clear();
        $this->assertNull(Redis::connection()->get($this->model->cacheKey()));
        $this->assertEquals([], $this->model->settings()->getStoredValue());
    }

    public function testUnsavedModelThrows(): void
    {
        $this->expectException(ModelSettingsException::class);
        $this->expectExceptionMessage('must be saved');

        (new User())->settings()->all();
    }
}
