<?php

namespace Glorand\Model\Settings\Traits;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Models\ModelSettings;
use Glorand\Model\Settings\SettingsManagerFactory;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Arr;

/**
 * @property array $settingsRules
 * @property array $defaultSettings
 * @property string $settingsDriver
 * @property string $settingsFieldName
 * @property array $settings
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
trait HasSettings
{
    private ?SettingsManagerContract $settingsManagerInstance = null;

    private $persistSettings = null;

    protected static function bootHasSettings()
    {
        static::saving(function ($model) {
            /** @var self $model */
            if ($model->getSettingsDriver() === 'field') {
                $model->fixSettingsValue();
            }
        });
    }

    /**
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
     */
    public function settings(): SettingsManagerContract
    {
        if (null === $this->settingsManagerInstance) {
            $this->settingsManagerInstance = app(SettingsManagerFactory::class)->make($this);
        }

        return $this->settingsManagerInstance;
    }

    public function getSettingsDriver(): string
    {
        return $this->settingsDriver ?? config('model_settings.driver');
    }

    public function getSettingsValue(): array
    {
        return $this->settings()->getStoredValue();
    }

    /**
     * @deprecated Use getSettingsValue() instead
     * Will be removed in the next major version
     *
     * @return array
     */
    public function getRules(): array
    {
        return $this->getSettingsRules();
    }

    public function getSettingsRules(): array
    {
        if (property_exists($this, 'settingsRules') && is_array($this->settingsRules)) {
            return $this->settingsRules;
        }

        return [];
    }

    public function getDefaultSettings(): array
    {
        if (property_exists($this, 'defaultSettings')
            && is_array($this->defaultSettings)) {
            return Arr::wrap($this->defaultSettings);
        } elseif (($defaultSettings = config('model_settings.defaultSettings.' . $this->getTable()))
            && is_array($defaultSettings)) {
            return Arr::wrap($defaultSettings);
        }

        return [];
    }

    public function getSettingsFieldName(): string
    {
        return $this->settingsFieldName ?? $this->driverConfig('field_name', 'settings');
    }

    public function isPersistSettings(): bool
    {
        return boolval($this->persistSettings ?? $this->driverConfig('persistent', true));
    }

    /**
     * @param bool $val
     */
    public function setPersistSettings(bool $val = true)
    {
        $this->persistSettings = $val;
    }

    public function fixSettingsValue()
    {
        $settingsFieldName = $this->getSettingsFieldName();
        $attributes = $this->getAttributes();
        if (Arr::has($attributes, $settingsFieldName)) {
            if (is_array($this->$settingsFieldName)) {
                $this->$settingsFieldName = json_encode($this->$settingsFieldName);
            }
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function modelSettings(): MorphOne
    {
        return $this->morphOne(ModelSettings::class, 'model');
    }

    public function getSettingsCacheKey(): string
    {
        return $this->driverConfig('cache_prefix', 'model_settings:') . $this->getTable() . '::' . $this->getKey();
    }

    public function cacheKey(?string $key = null): string
    {
        return sprintf(
            '%s%s:%s',
            $this->driverConfig('key_prefix', 'r-k-'),
            $this->getTable(),
            $this->getKey()
        ) . $key;
    }

    public function __call($name, $args)
    {
        if (isset($this->invokeSettingsBy) && $name === $this->invokeSettingsBy) {
            return $this->settings();
        }

        return call_user_func(parent::class . '::__call', $name, $args);
    }

    private function driverConfig(string $key, mixed $default = null): mixed
    {
        return config("model_settings.drivers.{$this->getSettingsDriver()}.{$key}", $default);
    }
}
