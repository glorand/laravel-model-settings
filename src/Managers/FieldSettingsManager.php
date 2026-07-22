<?php

namespace Glorand\Model\Settings\Managers;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Traits\HasSettings;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Casts\AsEnumArrayObject;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use stdClass;

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

        if ($this->model->exists && !array_key_exists($settingsFieldName, $this->model->getAttributes())) {
            throw new ModelSettingsException(
                "The [$settingsFieldName] column was not selected on this model instance; "
                . 'settings cannot be safely read or written. Reload the model without a partial select().'
            );
        }

        $rawValue = $this->model->getAttributeValue($settingsFieldName);

        if (is_array($rawValue)) {
            return $rawValue;
        }

        if ($rawValue instanceof Arrayable) {
            return $rawValue->toArray();
        }

        if ($rawValue instanceof stdClass) {
            return (array) $rawValue;
        }

        return $this->decodeJsonValue($rawValue);
    }

    /**
     * @param array $settings
     * @return SettingsManagerContract
     */
    public function apply(array $settings = []): SettingsManagerContract
    {
        $this->validate($settings);

        $settingsFieldName = $this->model->getSettingsFieldName();
        $this->model->{$settingsFieldName} = static::castsToNativeArray($this->model, $settingsFieldName)
            ? $settings
            : json_encode($settings);

        if ($this->model->isPersistSettings()) {
            $this->model->save();
        }

        return $this;
    }

    /**
     * Whether the model's cast for this field will natively store/return an array-like
     * value (so the manager must NOT pre-encode it to a JSON string itself).
     */
    public static function castsToNativeArray(Model $model, string $field): bool
    {
        if ($model->hasCast($field, ['array', 'json', 'object', 'collection'])) {
            return true;
        }

        $cast = $model->getCasts()[$field] ?? null;
        if (!is_string($cast)) {
            return false;
        }

        $castClass = strtok($cast, ':');

        return in_array($castClass, [
            AsArrayObject::class,
            AsCollection::class,
            AsEnumArrayObject::class,
            AsEnumCollection::class,
        ], true);
    }

    private function hasSettingsField(): bool
    {
        $connection = $this->getModelConnection();
        $fieldName = $this->model->getSettingsFieldName();

        // A finite TTL instead of rememberForever: nothing invalidates this key when the
        // column is added or dropped, and schema changes are rare enough that a daily
        // re-check is a fair trade against a permanently stale result.
        return Cache::remember(
            config('model_settings.drivers.field.cache_prefix', 'model_settings:')
                . $connection . '::' . $this->model->getTable() . '::' . $fieldName . '::has_field',
            now()->addDays(),
            function () use ($connection, $fieldName) {
                return Schema::connection($connection)->hasColumn(
                    $this->model->getTable(),
                    $fieldName
                );
            }
        );
    }
}
