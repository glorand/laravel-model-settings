<?php

namespace Glorand\Model\Settings\Managers;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Class FieldSettingsManager
 * @package Glorand\Model\Settings\Managers
 * @property \Illuminate\Database\Eloquent\Model|\Glorand\Model\Settings\Traits\HasSettings $model
 */
class FieldSettingsManager extends AbstractSettingsManager
{
    /**
     * @return array
     * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
     */
    public function getStoredValue(): array
    {
        $settingsFieldName = $this->model->getSettingsFieldName();
        if (!$this->hasSettingsField()) {
            throw new ModelSettingsException(
                "Unknown field ($settingsFieldName) on table {$this->model->getTable()}"
            );
        }

        $value = json_decode($this->model->getAttributeValue($settingsFieldName) ?? '[]', true);

        return is_array($value) ? $value : [];
    }

    /**
     * @param  array  $settings
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     */
    public function apply(array $settings = []): SettingsManagerContract
    {
        $this->validate($settings);

        $this->model->{$this->model->getSettingsFieldName()} = json_encode($settings);
        if ($this->model->isPersistSettings()) {
            $this->model->save();
        }

        return $this;
    }

    /**
     * @return bool
     */
    private function hasSettingsField(): bool
    {
        return Cache::remember(
            config('model_settings.drivers.field.cache_prefix', 'model_settings:')
                . $this->model->getTable() . '::has_field',
            now()->addDays(1),
            function () {
                return Schema::connection($this->model->getConnectionName() ?? config('database.default'))
                    ->hasColumn(
                        $this->model->getTable(),
                        $this->model->getSettingsFieldName()
                    );
            }
        );
    }
}
