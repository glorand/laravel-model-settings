<?php

namespace Glorand\Model\Settings\Tests;

use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Tests\Models\UserWithRedis as User;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Redis;
use RuntimeException;

final class RedisSettingsManagerTest extends TestCase
{
    protected User $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = User::first();
    }

    public function testMarker(): void
    {
        $this->assertTrue(true);
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testClearRemovesRedisKey(): void
    {
        $this->model->settings()->apply(['a' => 'b']);
        $this->assertNotNull(Redis::connection()->get($this->model->cacheKey()));

        $this->model->settings()->clear();
        $this->assertNull(Redis::connection()->get($this->model->cacheKey()));
        $this->assertEquals([], $this->model->settings()->getStoredValue());
    }

    /**
     * @throws BindingResolutionException
     */
    public function testUnsavedModelThrows(): void
    {
        $this->expectException(ModelSettingsException::class);
        $this->expectExceptionMessage('must be saved');

        (new User())->settings()->all();
    }

    /**
     * @throws BindingResolutionException
     */
    public function testUnsavedModelWithAssignedKeyThrows(): void
    {
        $this->expectException(ModelSettingsException::class);
        $this->expectExceptionMessage('must be saved');

        $user = new User();
        $user->id = 12345;
        $user->settings()->all();
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testReadingAMissingKeyDoesNotEmitADeprecationNotice(): void
    {
        $this->assertNull(Redis::connection()->get($this->model->cacheKey()));

        set_error_handler(function ($errNo, $errStr) {
            throw new RuntimeException($errStr);
        }, E_DEPRECATED);

        try {
            $this->assertEquals([], $this->model->settings()->getStoredValue());
        } finally {
            restore_error_handler();
        }
    }
}
