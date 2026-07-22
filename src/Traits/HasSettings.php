<?php

namespace Glorand\Model\Settings\Traits;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Models\ModelSettings;
use Glorand\Model\Settings\SettingsManagerFactory;
use Illuminate\Contracts\Container\BindingResolutionException;
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

    private ?bool $persistSettings = null;

    protected static function bootHasSettings(): void
    {
        static::saving(function ($model) {
            /** @var self $model */
            if ($model->getSettingsDriver() === 'field') {
                $model->fixSettingsValue();
            }
        });
    }

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
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

    /**
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
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

    public function setPersistSettings(bool $val = true): self
    {
        $this->persistSettings = $val;

        return $this;
    }

    public function fixSettingsValue(): void
    {
        $settingsFieldName = $this->getSettingsFieldName();
        if ($this->hasCast($settingsFieldName, ['array', 'json', 'object', 'collection'])) {
            return;
        }

        $attributes = $this->getAttributes();
        if (Arr::has($attributes, $settingsFieldName)) {
            if (is_array($this->$settingsFieldName)) {
                $this->$settingsFieldName = json_encode($this->$settingsFieldName);
            }
        }
    }

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

    /**
     * @return mixed
     * @throws ModelSettingsException
     * @throws BindingResolutionException
     */
    public function __call($name, $args)
    {
        if (isset($this->invokeSettingsBy) && $name === $this->invokeSettingsBy) {
            return $this->settings();
        }

        return parent::__call($name, $args);
    }

    private function driverConfig(string $key, mixed $default = null): mixed
    {
        return config("model_settings.drivers." . $this->getSettingsDriver() . ".$key", $default);
    }
}
