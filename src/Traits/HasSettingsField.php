<?php

namespace Glorand\Model\Settings\Traits;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Managers\FieldSettingsManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

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

    protected static function bootHasSettingsField()
    {
        static::saving(function ($model) {
            /** @var self $model */
            $model->fixSettingsValue();
        });
    }

    /**
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     * @throws ModelSettingsException
     */
    public function settings(): SettingsManagerContract
    {
        return new FieldSettingsManager($this);
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
     * @return array
     * @throws ModelSettingsException
     */
    public function getSettingsValue(): array
    {
        $settingsFieldName = $this->getSettingsFieldName();
        if (!$this->hasSettingsField()) {
            throw new ModelSettingsException("Unknown field ($settingsFieldName) on table {$this->getTable()}");
        }

        $value = json_decode($this->getAttributeValue($settingsFieldName) ?? '[]', true);

        return is_array($value) ? $value : [];
    }

    /**
     * @return string
     */
    public function getSettingsFieldName(): string
    {
        return $this->settingsFieldName ?? config('model_settings.settings_field_name');
    }

    /**
     * @return string
     */
    public function getConnectionName(): string
    {
        return $this->connection ?? config('database.default');
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


    /**
     * @return mixed
     */
    private function hasSettingsField()
    {
        return Cache::remember(
            config('model_settings.settings_table_cache_prefix') . '::has_field',
            now()->addDays(1),
            function () {
                return Schema::connection($this->getConnectionName())
                    ->hasColumn(
                        $this->getTable(),
                        $this->getSettingsFieldName()
                    );
            }
        );
    }

    abstract public function getTable();

    abstract public function getAttributes();

    abstract public function getAttributeValue($key);
}
