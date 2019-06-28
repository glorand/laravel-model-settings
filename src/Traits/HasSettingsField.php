<?php

namespace Glorand\Model\Settings\Traits;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Managers\FieldSettingsManager;

/**
 * Trait HasSettingsField
 * @package Glorand\Model\Settings\Traits
 * @property array $settings
 * @property string $settingsFieldName
 * @property boolean $persistSettings
 */
trait HasSettingsField
{
    use HasSettings;

    private $persistSettings = null;

    /**
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     * @throws ModelSettingsException
     */
    public function settings(): SettingsManagerContract
    {
        return new FieldSettingsManager($this);
    }

    /**
     * @return array
     * @throws ModelSettingsException
     */
    public function getSettingsValue(): array
    {
        $settingsFieldName = $this->getSettingsFieldName();
        $attributes = $this->getAttributes();
        if (!array_has($attributes, $settingsFieldName)) {
            throw new ModelSettingsException("Unknown field ($settingsFieldName) on table {$this->getTable()}");
        }

        return json_decode($this->getAttributeValue($settingsFieldName) ?? '[]', true);
    }

    /**
     * @return string
     */
    public function getSettingsFieldName(): string
    {
        return $this->settingsFieldName ?? config('model_settings.settings_field_name');
    }

    /**
     * @return bool
     */
    public function isPersistSettings(): bool
    {
        return boolval($this->persistSettings ?? config('model_settings.settings_persistent'));
    }

    /**
     * @param bool $val
     */
    public function setPersistSettings(bool $val = true)
    {
        $this->persistSettings = $val;
    }

    abstract public function getTable();

    abstract public function getAttributes();

    abstract public function getAttributeValue($key);
}
