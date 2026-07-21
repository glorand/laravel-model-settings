<?php

namespace Glorand\Model\Settings;

use Closure;
use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Illuminate\Database\Eloquent\Model;

final class SettingsManagerFactory
{
    /** @var array<string, Closure> */
    protected array $customCreators = [];

    public function extend(string $driver, Closure $callback): self
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * @throws ModelSettingsException
     */
    public function make(Model $model): SettingsManagerContract
    {
        $driver = method_exists($model, 'getSettingsDriver')
            ? $model->getSettingsDriver()
            : config('model_settings.driver');

        if (isset($this->customCreators[$driver])) {
            return $this->callCustomCreator($driver, $model);
        }

        $class = config("model_settings.drivers.$driver.class");
        if (!$class || !class_exists($class)) {
            throw new ModelSettingsException("Unsupported settings driver [$driver].");
        }

        $manager = new $class($model);
        if (!$manager instanceof SettingsManagerContract) {
            throw new ModelSettingsException(
                "Driver [$driver] must resolve to a " . SettingsManagerContract::class . '.'
            );
        }

        return $manager;
    }

    /**
     * @throws ModelSettingsException
     */
    protected function callCustomCreator(string $driver, Model $model): SettingsManagerContract
    {
        $manager = ($this->customCreators[$driver])($model);
        if (!$manager instanceof SettingsManagerContract) {
            throw new ModelSettingsException(
                "Custom driver [$driver] must return a " . SettingsManagerContract::class . '.'
            );
        }

        return $manager;
    }
}
