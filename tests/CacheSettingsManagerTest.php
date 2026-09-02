<?php

namespace Glorand\Model\Settings\Tests;

use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Tests\Models\UserWithCache as User;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException;
use RuntimeException;

final class CacheSettingsManagerTest extends TestCase
{
    protected User $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = User::first();
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testClearRemovesCacheKey(): void
    {
        $this->model->settings()->apply(['a' => 'b']);
        $this->assertNotNull(Cache::get($this->model->cacheKey()));

        $this->model->settings()->clear();
        $this->assertNull(Cache::get($this->model->cacheKey()));
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
        $this->assertNull(Cache::get($this->model->cacheKey()));

        set_error_handler(function ($errNo, $errStr) {
            throw new RuntimeException($errStr);
        }, E_DEPRECATED);

        try {
            $this->assertEquals([], $this->model->settings()->getStoredValue());
        } finally {
            restore_error_handler();
        }
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testSettingsAreStoredInCache(): void
    {
        $settings = ['key' => 'value', 'nested' => ['foo' => 'bar']];
        $this->model->settings()->apply($settings);

        $cached = Cache::get($this->model->cacheKey());
        $this->assertNotNull($cached);
        $this->assertEquals($settings, json_decode($cached, true));
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testSettingsCanBeRetrieved(): void
    {
        $settings = ['key' => 'value'];
        $this->model->settings()->apply($settings);

        $this->assertEquals($settings, $this->model->settings()->all());
        $this->assertEquals('value', $this->model->settings()->get('key'));
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testSetAndGet(): void
    {
        $this->model->settings()->set('name', 'Lorand');
        $this->assertEquals('Lorand', $this->model->settings()->get('name'));
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testHas(): void
    {
        $this->model->settings()->set('name', 'Lorand');
        $this->assertTrue($this->model->settings()->has('name'));
        $this->assertFalse($this->model->settings()->has('nonexistent'));
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testDelete(): void
    {
        $this->model->settings()->set('name', 'Lorand');
        $this->assertTrue($this->model->settings()->has('name'));

        $this->model->settings()->delete('name');
        $this->assertFalse($this->model->settings()->has('name'));
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testSetMultiple(): void
    {
        $this->model->settings()->setMultiple([
            'name' => 'Lorand',
            'email' => 'test@example.com',
        ]);

        $this->assertEquals('Lorand', $this->model->settings()->get('name'));
        $this->assertEquals('test@example.com', $this->model->settings()->get('email'));
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testDeleteMultiple(): void
    {
        $this->model->settings()->setMultiple([
            'name' => 'Lorand',
            'email' => 'test@example.com',
        ]);

        $this->model->settings()->deleteMultiple(['name', 'email']);
        $this->assertFalse($this->model->settings()->has('name'));
        $this->assertFalse($this->model->settings()->has('email'));
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testSettingsAreStoredForeverByDefault(): void
    {
        $this->model->settings()->apply(['key' => 'value']);

        $this->travel(1)->years();

        $this->assertEquals(['key' => 'value'], $this->model->settings()->getStoredValue());
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testConfiguredTtlExpiresTheSettings(): void
    {
        config()->set('model_settings.drivers.cache.ttl', 60);
        $this->model->settings()->apply(['key' => 'value']);

        $this->travel(59)->minutes();
        $this->assertEquals(['key' => 'value'], $this->model->settings()->getStoredValue());

        $this->travel(2)->minutes();
        $this->assertEquals([], $this->model->settings()->getStoredValue());
    }

    /**
     * @throws BindingResolutionException
     */
    public function testNonPositiveTtlThrowsInsteadOfDiscardingTheSettings(): void
    {
        config()->set('model_settings.drivers.cache.ttl', 0);

        $this->expectException(ModelSettingsException::class);
        $this->expectExceptionMessage('expected a positive number of minutes');

        $this->model->settings()->apply(['key' => 'value']);
    }

    /**
     * @throws BindingResolutionException
     */
    public function testNonNumericTtlThrows(): void
    {
        config()->set('model_settings.drivers.cache.ttl', 'an hour');

        $this->expectException(ModelSettingsException::class);
        $this->expectExceptionMessage('expected a positive number of minutes');

        $this->model->settings()->apply(['key' => 'value']);
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     */
    public function testAnEmptyStoreConfigFallsBackToTheDefaultStore(): void
    {
        // env('MODEL_SETTINGS_CACHE_STORE') yields '' - not null - for an empty .env line,
        // and Cache::store() resolves its default with ?? so '' would be taken as a store name.
        config()->set('model_settings.drivers.cache.store', '');

        $this->model->settings()->apply(['key' => 'value']);

        $this->assertNotNull(Cache::store()->get($this->model->cacheKey()));
        $this->assertEquals(['key' => 'value'], $this->model->settings()->getStoredValue());
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     */
    public function testSettingsAreWrittenToTheConfiguredStore(): void
    {
        config()->set('cache.stores.settings', ['driver' => 'array']);
        config()->set('model_settings.drivers.cache.store', 'settings');

        $this->model->settings()->apply(['key' => 'value']);

        $this->assertNotNull(Cache::store('settings')->get($this->model->cacheKey()));
        $this->assertNull(Cache::store()->get($this->model->cacheKey()));
        $this->assertEquals(['key' => 'value'], $this->model->settings()->getStoredValue());
    }
}
