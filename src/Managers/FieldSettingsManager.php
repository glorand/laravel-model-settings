<?php

namespace Glorand\Model\Settings\Managers;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Traits\HasSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * @property Model|HasSettings $model
 */
class FieldSettingsManager extends AbstractSettingsManager
{
    /**
     * @throws ModelSettingsException
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
     * @param array $settings
     * @return SettingsManagerContract
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

    private function hasSettingsField(): bool
    {
        return Cache::remember(
            config('model_settings.drivers.field.cache_prefix', 'model_settings:')
                . $this->model->getTable() . '::has_field',
            now()->addDays(),
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
