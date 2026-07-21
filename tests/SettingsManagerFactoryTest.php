<?php

namespace Glorand\Model\Settings\Tests;

use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Managers\FieldSettingsManager;
use Glorand\Model\Settings\Managers\RedisSettingsManager;
use Glorand\Model\Settings\Managers\TableSettingsManager;
use Glorand\Model\Settings\SettingsManagerFactory;
use Glorand\Model\Settings\Tests\Models\UserWithDeclaredDriver;
use Glorand\Model\Settings\Tests\Models\UserWithConfigDriver;
use Illuminate\Contracts\Container\BindingResolutionException;
use stdClass;

final class SettingsManagerFactoryTest extends TestCase
{
    public function testFactoryIsBoundAsSingleton(): void
    {
        $this->assertSame(
            app(SettingsManagerFactory::class),
            app(SettingsManagerFactory::class)
        );
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testMakeResolvesDefaultDriverFromConfig(): void
    {
        $manager = app(SettingsManagerFactory::class)->make(UserWithConfigDriver::first());

        $this->assertInstanceOf(FieldSettingsManager::class, $manager);
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testMakeResolvesConfiguredDriver(): void
    {
        $factory = app(SettingsManagerFactory::class);

        config()->set('model_settings.driver', 'table');
        $this->assertInstanceOf(TableSettingsManager::class, $factory->make(UserWithConfigDriver::first()));

        config()->set('model_settings.driver', 'redis');
        $this->assertInstanceOf(RedisSettingsManager::class, $factory->make(UserWithConfigDriver::first()));
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testModelDriverOverridesConfig(): void
    {
        config()->set('model_settings.driver', 'field');

        $manager = app(SettingsManagerFactory::class)->make(UserWithDeclaredDriver::first());

        $this->assertInstanceOf(RedisSettingsManager::class, $manager);
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function testExtendRegistersCustomDriver(): void
    {
        app(SettingsManagerFactory::class)->extend('custom', function ($model) {
            return new CustomSettingsManager($model);
        });
        config()->set('model_settings.driver', 'custom');

        $manager = app(SettingsManagerFactory::class)->make(UserWithConfigDriver::first());

        $this->assertInstanceOf(CustomSettingsManager::class, $manager);
    }

    /**
     * @throws BindingResolutionException
     */
    public function testUnknownDriverThrows(): void
    {
        config()->set('model_settings.driver', 'missing');

        $this->expectException(ModelSettingsException::class);
        $this->expectExceptionMessage('Unsupported settings driver [missing]');

        app(SettingsManagerFactory::class)->make(UserWithConfigDriver::first());
    }

    /**
     * @throws BindingResolutionException
     */
    public function testCustomCreatorMustReturnContract(): void
    {
        app(SettingsManagerFactory::class)->extend('broken', function () {
            return new stdClass();
        });
        config()->set('model_settings.driver', 'broken');

        $this->expectException(ModelSettingsException::class);
        $this->expectExceptionMessage('Custom driver [broken] must return a');

        app(SettingsManagerFactory::class)->make(UserWithConfigDriver::first());
    }
}
